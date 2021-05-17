#!/usr/bin/env python3
__author__ = 'Emil Bratt Boersting'
import os
from datetime import datetime
from time import sleep
try: # CHECK IF PILLOW IS INSTALLED
    from PIL import Image
except ModuleNotFoundError:
    print('No module found for Pillow')
    exit()
import PIL.ImageOps


def prepare_icon():
    format = "png"
    target_folder = 'prepared'
    relative_root_dir = os.getcwd()
    icon_folder_name = 'original'
    icon_directory = os.path.join(relative_root_dir, icon_folder_name)
    target_directory = os.path.join(relative_root_dir, target_folder)

    if not os.path.isdir(target_directory):
        os.mkdir(target_directory)

    for image in os.listdir(icon_directory):
        source_target = os.path.join(icon_directory,image)
        save_target =  os.path.join(target_directory,image)

        image = Image.open(source_target)
        inverted_image = PIL.ImageOps.invert( image.convert('L') )
        inverted_image = (image.convert('LA'))

        resized_image = inverted_image.resize((120, 120), Image.ANTIALIAS)
        resized_image.save(save_target)
        resized_image.close()

if __name__ == '__main__':
    prepare_icon()
