from PIL import Image, ImageFile
from sys import exit, stderr
from os.path import getsize, isfile, isdir, join
from os import remove, rename, walk, stat
from shutil import move
from argparse import ArgumentParser
from abc import ABCMeta, abstractmethod
 
class ProcessBase:
    """Abstract base class for file processors."""
    __metaclass__ = ABCMeta
 
    def __init__(self):
        self.extensions = []
        self.backupextension = 'backup'

    @abstractmethod
    def processfile(self, filename):
        """Abstract method which carries out the process on the specified file.
        Returns True if successful, False otherwise."""
        pass
 
class CompressImage(ProcessBase):
    """Processor which attempts to reduce image file size."""
    def __init__(self):
        ProcessBase.__init__(self)
        self.extensions = ['jpg', 'jpeg', 'png']
 
    def processfile(self, filename, Quality):
     
        """Renames the specified image to a backup path,
        and writes out the image again with optimal settings."""
        try:
            
            # Create a backup
            backupname = filename + '.' + self.backupextension
            
            
            if isfile(backupname):
                print 'Ignoring file "' + filename + '" for which existing backup file is present.'
                return False
 
            rename(filename, backupname)
        except Exception as e:
            stderr.write('Skipping file "' + filename + '" for which backup cannot be made: ' + str(e) + '\n')
            return False
 
        ok = False
 
        try:
        
            # Open the image
            with open(backupname, 'rb') as file:
                img = Image.open(file)
 
                # Check that it's a supported format
                format = str(img.format)
                if format != 'PNG' and format != 'JPEG':
                    print 'Ignoring file "' + filename + '" with unsupported format ' + format
                    return False
 
                # This line avoids problems that can arise saving larger JPEG files with PIL
                ImageFile.MAXBLOCK = img.size[0] * img.size[1]
                
                # The 'quality' option is ignored for PNG files
                img.save(filename, quality=int(float(Quality)), optimize=True)     
 
            # Check that we've actually made it smaller
        	origsize = getsize(backupname)
            newsize = getsize(filename)
 
            if newsize >= origsize:
                print 'Cannot further compress "' + filename + '".'
                return False
 			
 			
            # Successful compression
            ok = True
            
            remove(backupname)
            
        except Exception as e:
            stderr.write('Failure whilst processing "' + filename + '": ' + str(e) + '\n')
        finally:
            if not ok:
                try:
                    move(backupname, filename)
                except Exception as e:
                    stderr.write('ERROR: could not restore backup file for "' + filename + '": ' + str(e) + '\n')
 
        return ok
 
if __name__ == "__main__":
    # Argument parsing
    parser = ArgumentParser(description='Reduce file size of PNG and JPEG images.')
    parser.add_argument(
        'quality',
        help='File quality')
    parser.add_argument(
        'filename',
         help='File name')
 
    args = parser.parse_args()
 
    processor = CompressImage()
 	
    processor.processfile(args.filename, args.quality)
