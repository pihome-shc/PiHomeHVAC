# System Image Creation
Rather than remove the sd card, copy it, then reinstall the card to restart the system, it would be nice if you could backup the running system to a NAS and do incremental backups. This is a description of how this can be accomplished using a set of image manipulation utilities.

Thanks go to RonR for the original scripts.
If you want any further information about the scripts then visit https://forums.raspberrypi.com/viewtopic.php?t=332000

## Quick Start
1. Execute the install.sh bash script this will:
   * Update the MaxAir database to add a new table 'auto_image', which will detail the last execution of the image process.
   * Add a new Job Schedule to check on a daily basis if a image file is to be created.
   * Copy the files from the 'image_utils' directory to '/usr/local/bin' and set ownership/permissions.
   * Enable a new 'System Image' option in the Settings/System Maintenance menu.
   * Execution will create a multi gigabyte image file, which will typically be written to a NAS drive.
   * A folder will be needed in directory /mnt or /media as these aren’t included in the backup.
   * Image creation can be scheduled using the settings/System Maintenance/System Image option. This allows image creation on a periodic schedule and the deletion of aged images on a separate schedule.
