#!/usr/bin/env python3
import sys
import os
from PIL import Image

def flip_sprite_sheet(image_path):
    try:
        # Open the image and ensure it's in RGBA mode to preserve transparency
        with Image.open(image_path) as img:
            img = img.convert("RGBA")
            width, height = img.size

            # The height determines the slice size (e.g., 40x40, 80x80)
            slice_size = height

            if width % slice_size != 0:
                print(f"Warning: The image width ({width}px) is not a perfect multiple of its height ({height}px).")

            num_frames = width // slice_size

            # Create a new blank canvas with the exact same dimensions and transparent background
            flipped_sheet = Image.new("RGBA", (width, height), (0, 0, 0, 0))

            for i in range(num_frames):
                # Calculate the bounding box for the current frame
                left = i * slice_size
                upper = 0
                right = left + slice_size
                lower = height
                box = (left, upper, right, lower)

                # Extract the frame, flip it horizontally, and paste it onto the new canvas
                frame = img.crop(box)
                flipped_frame = frame.transpose(Image.FLIP_LEFT_RIGHT)
                flipped_sheet.paste(flipped_frame, box)

            # Overwrite the original file to allow seamless back-and-forth usage
            flipped_sheet.save(image_path, "PNG")
            print(f"Success! Flipped {num_frames} frames in '{os.path.basename(image_path)}'.")

    except Exception as e:
        print(f"Error processing {image_path}: {e}")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python3 mmrpg-sprite-flipper.py <path_to_sprite_sheet.png>")
        sys.exit(1)

    target_path = sys.argv[1]
    if os.path.exists(target_path):
        flip_sprite_sheet(target_path)
    else:
        print(f"File not found: {target_path}")
