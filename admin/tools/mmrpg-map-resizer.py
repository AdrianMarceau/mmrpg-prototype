import argparse
import re
import os

def resize_map(filepath, left, right, top, bottom):
    with open(filepath, 'r') as f:
        lines = f.readlines()

    header = []
    grid = []

    # 1. Separate Header/Metadata from the Grid
    for line in lines:
        if line.strip().startswith('['):
            grid.append(line.strip())
        else:
            header.append(line.rstrip('\n'))

    # 2. Parse the Grid into a 2D array
    parsed_grid = []
    for row in grid:
        # Split by comma but preserve the exact cell strings
        cells = [c.strip() for c in row.split(',')]
        parsed_grid.append(cells)

    orig_h = len(parsed_grid)
    orig_w = len(parsed_grid[0]) if orig_h > 0 else 0
    empty_cell = "[__]"

    # 3. Apply Vertical Resizing (Top/Bottom)
    if top > 0:
        for _ in range(top):
            parsed_grid.insert(0, [empty_cell] * orig_w)
    elif top < 0:
        parsed_grid = parsed_grid[abs(top):]

    if bottom > 0:
        for _ in range(bottom):
            parsed_grid.append([empty_cell] * orig_w)
    elif bottom < 0:
        parsed_grid = parsed_grid[:-abs(bottom)]

    # Update current height before horizontal resize
    current_h = len(parsed_grid)

    # 4. Apply Horizontal Resizing (Left/Right)
    for i in range(current_h):
        if left > 0:
            parsed_grid[i] = ([empty_cell] * left) + parsed_grid[i]
        elif left < 0:
            parsed_grid[i] = parsed_grid[i][abs(left):]

        if right > 0:
            parsed_grid[i] = parsed_grid[i] + ([empty_cell] * right)
        elif right < 0:
            parsed_grid[i] = parsed_grid[i][:-abs(right)]

    new_w = len(parsed_grid[0]) if parsed_grid else 0
    new_h = len(parsed_grid) if parsed_grid else 0

    # 5. Process Header Coordinates
    # Matches digits-digits bounded by (, ), space, comma, or dot
    # This ensures we match '10-14' or '8-12...12-16' but skip IDs like 'alt-01'
    coord_pattern = re.compile(r'(?<=[(\s,\.])(\d+)-(\d+)(?=[)\s,\.])')

    def update_coord(match):
        x, y = int(match.group(1)), int(match.group(2))
        new_x = x + left
        new_y = y + top
        return f"{new_x}-{new_y}"

    updated_header = []
    for line in header:
        if line.startswith('@size'):
            # Update the map size variable explicitly
            line = re.sub(r'(\d+)\s*x\s*(\d+)', f"{new_w} x {new_h}", line)
            updated_header.append(line)
        elif line.startswith('@'):
            # Update X-Y coordinates in all other @ definitions
            new_line = coord_pattern.sub(update_coord, line)
            updated_header.append(new_line)
        else:
            updated_header.append(line)

    # 6. Save to a new file to allow for review
    base, ext = os.path.splitext(filepath)
    out_path = f"{base}_resized{ext}"

    with open(out_path, 'w') as f:
        for line in updated_header:
            f.write(line + '\n')
        for row in parsed_grid:
            f.write(','.join(row) + '\n')

    print(f"Map successfully resized!")
    print(f"Original size: {orig_w} x {orig_h} | New size: {new_w} x {new_h}")
    print(f"Output saved to: {out_path}")

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description="Resize map grids and shift coordinates.")
    parser.add_argument('file', help="Path to the .map file")
    parser.add_argument('-left', type=int, default=0, help="Columns to add/remove on the left")
    parser.add_argument('-right', type=int, default=0, help="Columns to add/remove on the right")
    parser.add_argument('-top', type=int, default=0, help="Rows to add/remove on the top")
    parser.add_argument('-bottom', type=int, default=0, help="Rows to add/remove on the bottom")

    args = parser.parse_args()
    resize_map(args.file, args.left, args.right, args.top, args.bottom)