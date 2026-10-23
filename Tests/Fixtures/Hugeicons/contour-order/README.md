These MIT-licensed outlined SVG fixtures come from the exact Desiderio
cf2d91faa9d377e218fd9c94c11782b678f300cb cold build with
@hugeicons/core-free-icons 4.3.5 and the pinned Build/hugeicons/requirements.txt.

mac: macOS ARM64, Python 3.14, Node 26.5.0.
linux: Debian bookworm AMD64, Python 3.11.2, Node 24.22.1.

Each pair has identical path elements, attributes, coordinates and closed
contours. Skia returned two of the contours in a different order. The Linux
font reproduced the GitHub Actions asset checksum exactly. These fixtures
protect platform-independent font generation without rounding coordinates,
changing winding, or changing XML paint order.
