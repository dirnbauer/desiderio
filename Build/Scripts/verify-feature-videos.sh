#!/usr/bin/env bash

# Batch quality gate for the feature-video Hyperframes projects.
#
# Usage:
#   Build/Scripts/verify-feature-videos.sh
#   Build/Scripts/verify-feature-videos.sh records-list mcp-server
#
# Reports are additive and live under each project's qa/<run-id>/ directory.
# This script never renders or rewrites public media.

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PROJECTS_DIR="$ROOT_DIR/Build/Videos/features/projects"
PUBLIC_DIR="$ROOT_DIR/Resources/Public/Styleguide/Video"
HYPERFRAMES_VERSION="${HYPERFRAMES_VERSION:-0.7.49}"
ANIMATION_MAP_FRAMES="${ANIMATION_MAP_FRAMES:-6}"
ANIMATION_MAP_FPS="${ANIMATION_MAP_FPS:-30}"
RUN_ID="${QA_RUN_ID:-$(date -u '+%Y%m%dT%H%M%SZ')-$$}"

ANIMATION_HELPER_SOURCE=""
ANIMATION_HELPER_RUNNER=""
ANIMATION_HELPER_NODE_MODULES=""
ANIMATION_HELPER_NOTE=""
ANIMATION_HELPER_ERROR=""
W2H_VERIFY_SOURCE=""
HELPER_BUNDLE=""
OVERALL_STATUS=0

usage() {
    sed -n '3,10p' "$0"
}

cleanup() {
    if [[ -n "$HELPER_BUNDLE" && -d "$HELPER_BUNDLE" ]]; then
        rm -rf "$HELPER_BUNDLE"
    fi
}
trap cleanup EXIT

require_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "Required command not found: $1" >&2
        exit 2
    fi
}

print_command() {
    printf '$'
    while (($#)); do
        printf ' %q' "$1"
        shift
    done
    printf '\n'
}

run_logged() {
    local log_file="$1"
    local status
    shift

    {
        print_command "$@"
        "$@"
    } 2>&1 | tee "$log_file"
    status=${PIPESTATUS[0]}
    printf '\nexit_status=%s\n' "$status" | tee -a "$log_file"
    return "$status"
}

record_result() {
    local summary_file="$1"
    local status="$2"
    local check="$3"
    local detail="$4"

    printf '%-5s %-22s %s\n' "$status" "$check" "$detail" | tee -a "$summary_file"
}

locate_helper() {
    local explicit_path="$1"
    local relative_path="$2"
    local candidate
    local search_root
    local found=""

    if [[ -n "$explicit_path" ]]; then
        if [[ -f "$explicit_path" ]]; then
            printf '%s\n' "$explicit_path"
            return 0
        fi
        return 1
    fi

    for candidate in \
        "$ROOT_DIR/$relative_path" \
        "$ROOT_DIR/../webconsulting-skills/$relative_path" \
        "${HOME:-}/.codex/$relative_path" \
        "${HOME:-}/.agents/$relative_path" \
        "${HOME:-}/projects/webconsulting-skills/$relative_path"
    do
        if [[ -f "$candidate" ]]; then
            printf '%s\n' "$candidate"
            return 0
        fi
    done

    for search_root in "$ROOT_DIR/.." "${HOME:-}/.codex" "${HOME:-}/.agents"; do
        [[ -d "$search_root" ]] || continue
        found=""
        while IFS= read -r candidate; do
            found="$candidate"
            break
        done < <(find "$search_root" -maxdepth 14 -type f -path "*/$relative_path" -print 2>/dev/null)
        if [[ -n "$found" ]]; then
            printf '%s\n' "$found"
            return 0
        fi
    done

    return 1
}

find_compatible_producer_modules() {
    local search_root
    local manifest
    local package_version

    for search_root in \
        "$ROOT_DIR/node_modules" \
        "${HOME:-}/.npm/_npx" \
        "${HOME:-}/.codex/plugins/cache"
    do
        [[ -d "$search_root" ]] || continue
        while IFS= read -r manifest; do
            package_version="$(node -e 'const p=require(process.argv[1]); process.stdout.write(p.version || "")' "$manifest" 2>/dev/null || true)"
            if [[ "$package_version" == "$HYPERFRAMES_VERSION" ]]; then
                dirname "$(dirname "$(dirname "$manifest")")"
                return 0
            fi
        done < <(find "$search_root" -maxdepth 8 -type f -path '*/node_modules/@hyperframes/producer/package.json' -print 2>/dev/null)
    done

    return 1
}

prepare_animation_helper() {
    local helper_dir
    local install_log

    if ! ANIMATION_HELPER_SOURCE="$(locate_helper "${ANIMATION_MAP_HELPER:-}" 'skills/hyperframes/scripts/animation-map.mjs')"; then
        ANIMATION_HELPER_ERROR="animation-map.mjs was not found; set ANIMATION_MAP_HELPER to its absolute path"
        return 1
    fi

    helper_dir="$(dirname "$ANIMATION_HELPER_SOURCE")"
    if [[ ! -f "$helper_dir/package-loader.mjs" ]]; then
        ANIMATION_HELPER_ERROR="package-loader.mjs is missing beside $ANIMATION_HELPER_SOURCE"
        return 1
    fi

    HELPER_BUNDLE="$(mktemp -d "${TMPDIR:-/tmp}/feature-video-qa.XXXXXX")"
    cp "$ANIMATION_HELPER_SOURCE" "$HELPER_BUNDLE/animation-map.mjs"
    cp "$helper_dir/package-loader.mjs" "$HELPER_BUNDLE/package-loader.mjs"
    printf '{"name":"@hyperframes/cli","version":"%s","type":"module"}\n' \
        "$HYPERFRAMES_VERSION" > "$HELPER_BUNDLE/package.json"
    ANIMATION_HELPER_RUNNER="$HELPER_BUNDLE/animation-map.mjs"

    if ANIMATION_HELPER_NODE_MODULES="$(find_compatible_producer_modules)"; then
        ANIMATION_HELPER_NOTE="reused @hyperframes/producer@$HYPERFRAMES_VERSION from $ANIMATION_HELPER_NODE_MODULES"
        return 0
    fi

    install_log="$HELPER_BUNDLE/npm-install.log"
    if npm install \
        --silent \
        --no-audit \
        --no-fund \
        --ignore-scripts \
        --no-save \
        --prefix "$HELPER_BUNDLE" \
        "@hyperframes/producer@$HYPERFRAMES_VERSION" >"$install_log" 2>&1
    then
        ANIMATION_HELPER_NODE_MODULES="$HELPER_BUNDLE/node_modules"
        ANIMATION_HELPER_NOTE="installed temporary @hyperframes/producer@$HYPERFRAMES_VERSION"
        return 0
    fi

    ANIMATION_HELPER_ERROR="temporary @hyperframes/producer install failed: $(tail -n 5 "$install_log" | tr '\n' ' ')"
    return 1
}

read_composition_metadata() {
    node -e '
const fs = require("fs");
const data = JSON.parse(fs.readFileSync(process.argv[1], "utf8"));
const list = Array.isArray(data.compositions) ? data.compositions : [];
const main = list.find((item) => item.id === "main") || list[0];
if (!main || !(Number(main.duration) > 0)) throw new Error("main composition duration is missing");
if (!(Number(main.width) > 0) || !(Number(main.height) > 0)) throw new Error("main composition dimensions are missing");
process.stdout.write([main.duration, main.width, main.height].join("\t"));
' "$1"
}

snapshot_frame_count() {
    awk -v duration="$1" 'BEGIN {
        half = duration / 2;
        frames = int(half);
        if (half > frames) frames++;
        if (frames < 12) frames = 12;
        print frames;
    }'
}

summarize_animation_map() {
    node -e '
const fs = require("fs");
const report = JSON.parse(fs.readFileSync(process.argv[1], "utf8"));
if (!(Number(report.duration) > 0)) throw new Error("animation-map duration is missing");
if (!Array.isArray(report.tweens)) throw new Error("animation-map tweens are missing");
const flags = {};
let missingBoxes = 0;
for (const tween of report.tweens) {
  for (const flag of tween.flags || []) flags[flag] = (flags[flag] || 0) + 1;
  for (const box of tween.bboxes || []) if (box.missing) missingBoxes++;
}
console.log(`duration=${report.duration}`);
console.log(`total_tweens=${Number(report.totalTweens) || 0}`);
console.log(`mapped_tweens=${Number(report.mappedTweens) || 0}`);
console.log(`dead_zones=${Array.isArray(report.deadZones) ? report.deadZones.length : 0}`);
console.log(`missing_bboxes=${missingBoxes}`);
console.log(`flags=${Object.keys(flags).length ? JSON.stringify(flags) : "none"}`);
if (missingBoxes > 0) process.exitCode = 1;
' "$1"
}

verify_public_assets() {
    local slug="$1"
    local duration="$2"
    local width="$3"
    local height="$4"
    local qa_dir="$5"
    local report="$qa_dir/public-assets.txt"
    local mp4="$PUBLIC_DIR/$slug-feature-video.mp4"
    local poster="$PUBLIC_DIR/$slug-feature-video-poster.webp"
    local vtt="$PUBLIC_DIR/$slug-feature-video.en.vtt"
    local mp4_json="$qa_dir/public-mp4.ffprobe.json"
    local poster_json="$qa_dir/public-poster.ffprobe.json"
    local artifact
    local failed=0

    : > "$report"
    printf 'MP4: %s\nPoster: %s\nVTT: %s\n\n' "$mp4" "$poster" "$vtt" >> "$report"

    for artifact in "$mp4" "$poster" "$vtt"; do
        if [[ ! -s "$artifact" ]]; then
            printf 'FAIL missing or empty: %s\n' "$artifact" >> "$report"
            failed=1
        fi
    done
    if ((failed)); then
        cat "$report"
        return 1
    fi

    if ! ffprobe \
        -v error \
        -show_entries 'format=duration,format_name,size:stream=index,codec_type,codec_name,width,height,r_frame_rate,sample_rate,channels' \
        -of json \
        "$mp4" > "$mp4_json" 2>> "$report"
    then
        printf 'FAIL ffprobe could not read the MP4\n' >> "$report"
        cat "$report"
        return 1
    fi

    if ! ffprobe \
        -v error \
        -show_entries 'format=duration,format_name,size:stream=index,codec_type,codec_name,width,height' \
        -of json \
        "$poster" > "$poster_json" 2>> "$report"
    then
        printf 'FAIL ffprobe could not read the poster\n' >> "$report"
        cat "$report"
        return 1
    fi

    if ! node - "$mp4_json" "$poster_json" "$vtt" "$duration" "$width" "$height" >> "$report" 2>&1 <<'NODE'
const fs = require("fs");
const [mp4Path, posterPath, vttPath, expectedDurationRaw, expectedWidthRaw, expectedHeightRaw] = process.argv.slice(2);
const mp4 = JSON.parse(fs.readFileSync(mp4Path, "utf8"));
const poster = JSON.parse(fs.readFileSync(posterPath, "utf8"));
const vtt = fs.readFileSync(vttPath, "utf8").replace(/^\uFEFF/, "").replace(/\r\n?/g, "\n");
const expectedDuration = Number(expectedDurationRaw);
const expectedWidth = Number(expectedWidthRaw);
const expectedHeight = Number(expectedHeightRaw);
const failures = [];

const mp4Video = (mp4.streams || []).find((stream) => stream.codec_type === "video");
const mp4Audio = (mp4.streams || []).find((stream) => stream.codec_type === "audio");
const posterVideo = (poster.streams || []).find((stream) => stream.codec_type === "video");
const duration = Number(mp4.format && mp4.format.duration);
const formatName = String((mp4.format && mp4.format.format_name) || "");

if (!Number.isFinite(duration) || duration <= 0) failures.push("MP4 duration is missing or invalid");
if (!formatName.split(",").includes("mp4")) failures.push(`MP4 container is unexpected: ${formatName || "unknown"}`);
if (!mp4Video) failures.push("MP4 has no video stream");
if (!mp4Audio) failures.push("MP4 has no narration/audio stream");
if (mp4Video && mp4Video.codec_name !== "h264") failures.push(`MP4 video codec is ${mp4Video.codec_name}, expected h264`);
if (mp4Audio && mp4Audio.codec_name !== "aac") failures.push(`MP4 audio codec is ${mp4Audio.codec_name}, expected aac`);
if (mp4Video && (Number(mp4Video.width) !== expectedWidth || Number(mp4Video.height) !== expectedHeight)) {
  failures.push(`MP4 dimensions are ${mp4Video.width}x${mp4Video.height}, expected ${expectedWidth}x${expectedHeight}`);
}
if (Number.isFinite(duration) && Math.abs(duration - expectedDuration) > 1.0) {
  failures.push(`MP4 duration ${duration.toFixed(3)}s differs from composition ${expectedDuration.toFixed(3)}s by more than 1.0s`);
}

if (!posterVideo) failures.push("poster has no image/video stream");
if (posterVideo && posterVideo.codec_name !== "webp") failures.push(`poster codec is ${posterVideo.codec_name}, expected webp`);
if (posterVideo && (Number(posterVideo.width) !== expectedWidth || Number(posterVideo.height) !== expectedHeight)) {
  failures.push(`poster dimensions are ${posterVideo.width}x${posterVideo.height}, expected ${expectedWidth}x${expectedHeight}`);
}

if (!/^WEBVTT(?:[ \t].*)?\n/.test(vtt)) failures.push("VTT does not start with a WEBVTT header");
const cueLines = vtt.split("\n").filter((line) => line.includes("-->"));
if (cueLines.length === 0) failures.push("VTT contains no timed cues");

function timestampToSeconds(value) {
  const parts = value.trim().split(":").map(Number);
  if (parts.some((part) => !Number.isFinite(part))) return NaN;
  if (parts.length === 3) return parts[0] * 3600 + parts[1] * 60 + parts[2];
  if (parts.length === 2) return parts[0] * 60 + parts[1];
  return NaN;
}

let lastCueEnd = 0;
for (const line of cueLines) {
  const endToken = line.split("-->")[1].trim().split(/\s+/)[0];
  const end = timestampToSeconds(endToken);
  if (!Number.isFinite(end)) failures.push(`invalid VTT cue timestamp: ${line}`);
  else lastCueEnd = Math.max(lastCueEnd, end);
}
if (Number.isFinite(duration) && lastCueEnd > duration + 0.5) {
  failures.push(`last VTT cue ends at ${lastCueEnd.toFixed(3)}s, after MP4 duration ${duration.toFixed(3)}s`);
}

console.log(`mp4_duration=${Number.isFinite(duration) ? duration.toFixed(3) : "unknown"}s`);
console.log(`mp4_size=${mp4.format && mp4.format.size ? mp4.format.size : "unknown"} bytes`);
console.log(`mp4_video=${mp4Video ? `${mp4Video.codec_name} ${mp4Video.width}x${mp4Video.height} ${mp4Video.r_frame_rate || "unknown-fps"}` : "missing"}`);
console.log(`mp4_audio=${mp4Audio ? `${mp4Audio.codec_name} ${mp4Audio.sample_rate || "unknown-Hz"}Hz ${mp4Audio.channels || "unknown"}ch` : "missing"}`);
console.log(`poster=${posterVideo ? `${posterVideo.codec_name} ${posterVideo.width}x${posterVideo.height}` : "missing"}`);
console.log(`poster_size=${poster.format && poster.format.size ? poster.format.size : "unknown"} bytes`);
console.log(`vtt_cues=${cueLines.length}`);
console.log(`vtt_last_cue_end=${lastCueEnd.toFixed(3)}s`);

if (failures.length) {
  for (const failure of failures) console.error(`FAIL ${failure}`);
  process.exit(1);
}
console.log("PASS public MP4, poster, and VTT metadata");
NODE
    then
        cat "$report"
        return 1
    fi

    cat "$report"
    return 0
}

if (($#)) && [[ "$1" == "-h" || "$1" == "--help" ]]; then
    usage
    exit 0
fi

if [[ ! "$RUN_ID" =~ ^[A-Za-z0-9._-]+$ ]]; then
    echo "QA_RUN_ID contains unsupported characters: $RUN_ID" >&2
    exit 2
fi

require_command awk
require_command ffprobe
require_command find
require_command node
require_command npm
require_command npx
require_command tee

if [[ ! -d "$PROJECTS_DIR" ]]; then
    echo "Feature-video projects directory does not exist: $PROJECTS_DIR" >&2
    exit 2
fi

slugs=()
if (($#)); then
    slugs=("$@")
else
    while IFS= read -r slug; do
        slugs+=("$slug")
    done < <(find "$PROJECTS_DIR" -mindepth 1 -maxdepth 1 -type d -exec basename {} \; | sort)
fi

if ((${#slugs[@]} == 0)); then
    echo "No feature-video projects found in $PROJECTS_DIR" >&2
    exit 1
fi

if ! W2H_VERIFY_SOURCE="$(locate_helper "${W2H_VERIFY_HELPER:-}" 'skills/website-to-hyperframes/scripts/w2h-verify.mjs')"; then
    W2H_VERIFY_SOURCE=""
fi

prepare_animation_helper || true

for slug in "${slugs[@]}"; do
    project="$PROJECTS_DIR/$slug"
    qa_dir="$project/qa/$RUN_ID"
    summary_file="$qa_dir/summary.txt"
    project_failed=0
    duration=""
    width=""
    height=""
    frames=""

    if [[ ! "$slug" =~ ^[a-z0-9][a-z0-9-]*$ ]]; then
        echo "Invalid feature slug: $slug" >&2
        OVERALL_STATUS=1
        continue
    fi
    if [[ ! -f "$project/index.html" ]]; then
        echo "Missing Hyperframes project for $slug: $project/index.html" >&2
        OVERALL_STATUS=1
        continue
    fi
    if [[ -e "$qa_dir" ]]; then
        echo "QA report directory already exists; refusing to overwrite it: $qa_dir" >&2
        OVERALL_STATUS=1
        continue
    fi

    mkdir -p "$qa_dir"
    {
        printf 'Feature video QA\n'
        printf 'slug=%s\n' "$slug"
        printf 'project=%s\n' "$project"
        printf 'run_id=%s\n' "$RUN_ID"
        printf 'hyperframes_version=%s\n\n' "$HYPERFRAMES_VERSION"
    } > "$summary_file"

    echo
    echo "=== QA: $slug ==="
    echo "Reports: $qa_dir"

    print_command npx --yes "hyperframes@$HYPERFRAMES_VERSION" compositions "$project" --json > "$qa_dir/compositions.txt"
    if npx --yes "hyperframes@$HYPERFRAMES_VERSION" compositions "$project" --json \
        > "$qa_dir/compositions.json" 2>> "$qa_dir/compositions.txt"
    then
        printf '\nexit_status=0\n' >> "$qa_dir/compositions.txt"
        metadata="$(read_composition_metadata "$qa_dir/compositions.json" 2>> "$qa_dir/compositions.txt")"
        metadata_status=$?
        if ((metadata_status == 0)); then
            IFS=$'\t' read -r duration width height <<< "$metadata"
            frames="$(snapshot_frame_count "$duration")"
            record_result "$summary_file" PASS compositions "duration=${duration}s canvas=${width}x${height}; canonical_frames=$frames"
        else
            record_result "$summary_file" FAIL compositions "JSON did not expose valid main composition metadata"
            project_failed=1
        fi
    else
        compositions_status=$?
        printf '\nexit_status=%s\n' "$compositions_status" >> "$qa_dir/compositions.txt"
        record_result "$summary_file" FAIL compositions "Hyperframes compositions command failed"
        project_failed=1
    fi

    if run_logged "$qa_dir/lint.txt" \
        npx --yes "hyperframes@$HYPERFRAMES_VERSION" lint "$project"
    then
        record_result "$summary_file" PASS lint "see lint.txt"
    else
        record_result "$summary_file" FAIL lint "see lint.txt"
        project_failed=1
    fi

    if run_logged "$qa_dir/validate.txt" \
        npx --yes "hyperframes@$HYPERFRAMES_VERSION" validate "$project"
    then
        record_result "$summary_file" PASS validate "see validate.txt"
    else
        record_result "$summary_file" FAIL validate "see validate.txt"
        project_failed=1
    fi

    inspect_samples="${frames:-12}"
    if run_logged "$qa_dir/inspect.txt" \
        npx --yes "hyperframes@$HYPERFRAMES_VERSION" inspect "$project" --samples "$inspect_samples"
    then
        record_result "$summary_file" PASS inspect "samples=$inspect_samples; see inspect.txt"
    else
        record_result "$summary_file" FAIL inspect "samples=$inspect_samples; see inspect.txt"
        project_failed=1
    fi

    if [[ -n "$frames" ]]; then
        if run_logged "$qa_dir/snapshot.txt" \
            npx --yes "hyperframes@$HYPERFRAMES_VERSION" snapshot "$project" \
            --frames "$frames" \
            --output "$qa_dir/snapshots" \
            --describe false
        then
            actual_frames="$(find "$qa_dir/snapshots" -maxdepth 1 -type f -name 'frame-*.png' | wc -l | tr -d '[:space:]')"
            if [[ "$actual_frames" == "$frames" ]]; then
                record_result "$summary_file" PASS snapshots "$actual_frames canonical frames in snapshots/"
            else
                record_result "$summary_file" FAIL snapshots "expected $frames canonical frames, found $actual_frames"
                project_failed=1
            fi
        else
            record_result "$summary_file" FAIL snapshots "snapshot command failed; see snapshot.txt"
            project_failed=1
        fi
    else
        printf 'SKIP: composition duration unavailable\n' > "$qa_dir/snapshot.txt"
        record_result "$summary_file" FAIL snapshots "composition duration unavailable"
        project_failed=1
    fi

    if [[ -n "$ANIMATION_HELPER_RUNNER" && -n "$ANIMATION_HELPER_NODE_MODULES" ]]; then
        {
            printf 'source_helper=%s\n' "$ANIMATION_HELPER_SOURCE"
            printf 'dependency_setup=%s\n' "$ANIMATION_HELPER_NOTE"
        } > "$qa_dir/animation-map-setup.txt"
        if run_logged "$qa_dir/animation-map.txt" \
            env HYPERFRAMES_SKILL_NODE_MODULES="$ANIMATION_HELPER_NODE_MODULES" \
            node "$ANIMATION_HELPER_RUNNER" "$project" \
            --frames "$ANIMATION_MAP_FRAMES" \
            --out "$qa_dir/animation-map" \
            --width "${width:-1280}" \
            --height "${height:-720}" \
            --fps "$ANIMATION_MAP_FPS"
        then
            if [[ -s "$qa_dir/animation-map/animation-map.json" ]] && \
                summarize_animation_map "$qa_dir/animation-map/animation-map.json" \
                    > "$qa_dir/animation-map-summary.txt" 2>&1
            then
                cat "$qa_dir/animation-map-summary.txt"
                record_result "$summary_file" PASS animation-map "JSON generated; flags/dead zones retained for review"
            else
                cat "$qa_dir/animation-map-summary.txt" 2>/dev/null || true
                record_result "$summary_file" FAIL animation-map "missing/invalid JSON or missing sampled bounding boxes"
                project_failed=1
            fi
        else
            record_result "$summary_file" FAIL animation-map "helper failed; see animation-map.txt"
            project_failed=1
        fi
    else
        printf 'FAIL %s\n' "$ANIMATION_HELPER_ERROR" > "$qa_dir/animation-map.txt"
        record_result "$summary_file" FAIL animation-map "$ANIMATION_HELPER_ERROR"
        project_failed=1
    fi

    if [[ -n "$W2H_VERIFY_SOURCE" ]]; then
        if run_logged "$qa_dir/w2h-verify.txt" node "$W2H_VERIFY_SOURCE" "$project"; then
            record_result "$summary_file" PASS w2h-verify "see w2h-verify.txt"
        else
            record_result "$summary_file" FAIL w2h-verify "see w2h-verify.txt"
            project_failed=1
        fi
    else
        printf 'FAIL w2h-verify.mjs was not found; set W2H_VERIFY_HELPER to its absolute path\n' \
            > "$qa_dir/w2h-verify.txt"
        record_result "$summary_file" FAIL w2h-verify "helper not found"
        project_failed=1
    fi

    if [[ -n "$duration" ]]; then
        if verify_public_assets "$slug" "$duration" "$width" "$height" "$qa_dir"; then
            record_result "$summary_file" PASS public-assets "MP4/poster/VTT exist and metadata matches"
        else
            record_result "$summary_file" FAIL public-assets "see public-assets.txt"
            project_failed=1
        fi
    else
        printf 'FAIL composition metadata unavailable\n' > "$qa_dir/public-assets.txt"
        record_result "$summary_file" FAIL public-assets "composition metadata unavailable"
        project_failed=1
    fi

    if ((project_failed)); then
        printf '\nRESULT=FAIL\n' | tee -a "$summary_file"
        OVERALL_STATUS=1
    else
        printf '\nRESULT=PASS\n' | tee -a "$summary_file"
    fi
done

if ((OVERALL_STATUS)); then
    echo
    echo "One or more feature-video QA runs failed. Inspect each qa/$RUN_ID/summary.txt."
else
    echo
    echo "All feature-video QA runs passed. Reports are under each project at qa/$RUN_ID/."
fi

exit "$OVERALL_STATUS"
