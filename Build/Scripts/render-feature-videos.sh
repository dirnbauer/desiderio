#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PROJECTS_DIR="$ROOT_DIR/Build/Videos/features/projects"
PUBLIC_DIR="$ROOT_DIR/Resources/Public/Styleguide/Video"
HYPERFRAMES_VERSION="${HYPERFRAMES_VERSION:-0.7.49}"

if (($#)); then
    slugs=("$@")
else
    slugs=()
    while IFS= read -r slug; do
        slugs+=("$slug")
    done < <(find "$PROJECTS_DIR" -mindepth 1 -maxdepth 1 -type d -exec basename {} \; | sort)
fi

if ((${#slugs[@]} == 0)); then
    echo "No feature-video projects found in $PROJECTS_DIR" >&2
    exit 1
fi

mkdir -p "$PUBLIC_DIR"

for slug in "${slugs[@]}"; do
    project="$PROJECTS_DIR/$slug"
    raw="$project/renders/$slug-feature-video-raw.mp4"
    public_mp4="$PUBLIC_DIR/$slug-feature-video.mp4"
    public_poster="$PUBLIC_DIR/$slug-feature-video-poster.webp"
    public_vtt="$PUBLIC_DIR/$slug-feature-video.en.vtt"
    poster_png="$project/renders/$slug-feature-video-poster.png"

    if [[ ! -f "$project/index.html" ]]; then
        echo "Missing Hyperframes project for $slug: $project/index.html" >&2
        exit 1
    fi

    mkdir -p "$project/renders"

    (
        cd "$project"
        npx --yes "hyperframes@$HYPERFRAMES_VERSION" lint .
        npx --yes "hyperframes@$HYPERFRAMES_VERSION" validate .
        npx --yes "hyperframes@$HYPERFRAMES_VERSION" inspect . --samples 15
        npx --yes "hyperframes@$HYPERFRAMES_VERSION" render \
            --output "renders/$slug-feature-video-raw.mp4" \
            --quality standard \
            --fps 30 \
            --workers 1
    )

    ffmpeg -hide_banner -loglevel error -y \
        -i "$raw" \
        -c:v libx264 \
        -preset medium \
        -crf 18 \
        -pix_fmt yuv420p \
        -r 25 \
        -c:a aac \
        -b:a 192k \
        -ar 48000 \
        -ac 2 \
        -movflags +faststart \
        "$public_mp4"

    duration="$(ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 "$public_mp4")"
    poster_time="$(awk -v duration="$duration" 'BEGIN { time = duration - 1.5; if (time < 0) time = 0; printf "%.3f", time }')"

    ffmpeg -hide_banner -loglevel error -y \
        -ss "$poster_time" \
        -i "$public_mp4" \
        -frames:v 1 \
        -vf "scale=1280:720" \
        "$poster_png"

    magick "$poster_png" -quality 86 "$public_poster"
    rm "$poster_png"

    cp "$project/$slug.en.vtt" "$public_vtt"

    echo "Rendered $slug: $public_mp4"
done
