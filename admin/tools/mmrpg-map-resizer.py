import argparse
import re
import os

def resize_map(filepath, left, right, top, bottom):
    with open(filepath, 'r') as f:
        lines = f.readlines()

    blocks = []
    current_text = []
    current_grid = []

    # 1. Parse the file into sequential blocks to preserve multi-layer structure
    for line in lines:
        stripped = line.strip()
        if stripped.startswith('['):
            # If we were collecting text, save it as a text block and clear it
            if current_text:
                blocks.append({'type': 'text', 'lines': current_text})
                current_text = []

            # Split by comma but preserve the exact cell strings
            cells = [c.strip() for c in stripped.split(',')]
            current_grid.append(cells)
        else:
            # If we were collecting a grid, save it as a grid block and clear it
            if current_grid:
                blocks.append({'type': 'grid', 'rows': current_grid})
                current_grid = []

            current_text.append(line.rstrip('\n'))

    # Append any remaining blocks at the end of the file
    if current_text:
        blocks.append({'type': 'text', 'lines': current_text})
    if current_grid:
        blocks.append({'type': 'grid', 'rows': current_grid})

    orig_w, orig_h = 0, 0
    new_w, new_h = 0, 0
    empty_cell = "[__]"
    found_first_grid = False

    # 2. Apply Resizing to ALL grid blocks
    for block in blocks:
        if block['type'] == 'grid':
            grid = block['rows']

            # Capture original dimensions for the summary output based on the first layer
            curr_orig_h = len(grid)
            curr_orig_w = len(grid[0]) if curr_orig_h > 0 else 0
            if not found_first_grid:
                orig_h, orig_w = curr_orig_h, curr_orig_w
                found_first_grid = True

            # Apply Vertical Resizing (Top/Bottom)
            if top > 0:
                for _ in range(top):
                    grid.insert(0, [empty_cell] * curr_orig_w)
            elif top < 0:
                grid = grid[abs(top):]

            if bottom > 0:
                for _ in range(bottom):
                    grid.append([empty_cell] * curr_orig_w)
            elif bottom < 0:
                grid = grid[:-abs(bottom)]

            current_h = len(grid)

            # Apply Horizontal Resizing (Left/Right)
            for i in range(current_h):
                if left > 0:
                    grid[i] = ([empty_cell] * left) + grid[i]
                elif left < 0:
                    grid[i] = grid[i][abs(left):]

                if right > 0:
                    grid[i] = grid[i] + ([empty_cell] * right)
                elif right < 0:
                    grid[i] = grid[i][:-abs(right)]

            # Save the new grid dimensions
            new_h = len(grid)
            new_w = len(grid[0]) if new_h > 0 else 0

            block['rows'] = grid

    # 3. Process Coordinates in ALL text blocks
    # Matches digits-digits bounded by (, ), space, comma, or dot
    coord_pattern = re.compile(r'(?<=[(\s,\.])(\d+)-(\d+)(?=[)\s,\.])')

    def update_coord(match):
        x, y = int(match.group(1)), int(match.group(2))
        new_x = x + left
        new_y = y + top
        return f"{new_x}-{new_y}"

    for block in blocks:
        if block['type'] == 'text':
            updated_lines = []
            for line in block['lines']:
                if line.startswith('@size'):
                    # Update the map size variable explicitly
                    line = re.sub(r'(\d+)\s*x\s*(\d+)', f"{new_w} x {new_h}", line)
                    updated_lines.append(line)
                elif line.startswith('@'):
                    # Update X-Y coordinates in all other @ definitions
                    new_line = coord_pattern.sub(update_coord, line)
                    updated_lines.append(new_line)
                else:
                    updated_lines.append(line)
            block['lines'] = updated_lines

    # 4. Save to a new file, iterating through our sequential blocks
    base, ext = os.path.splitext(filepath)
    out_path = f"{base}_resized{ext}"

    with open(out_path, 'w') as f:
        for block in blocks:
            if block['type'] == 'text':
                for line in block['lines']:
                    f.write(line + '\n')
            elif block['type'] == 'grid':
                for row in block['rows']:
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