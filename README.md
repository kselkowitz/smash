# smash
SMASH monitors specific types of SNAPsolution users to ensure MFA is enabled

1) Load the script to your QoS server (suggest /usr/local/scripts/smash.php)

2) Edit constants at top of file (define lines)

3) Make the script executable 
chmod +x smash.php

4) Create cron job 
edit /etc/crontab, add line like
0 9 * * * root /usr/local/scripts/smash.php 0
