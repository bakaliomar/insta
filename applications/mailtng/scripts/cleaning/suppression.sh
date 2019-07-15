#!/bin/bash
/opt/jdk1.8.0_131/bin/java -XX:-UseGCOverheadLimit -Xms2024M -Xmx5024M -jar  /var/mailtng/applications/mailtng/scripts/cleaning/main.jar $1 $2 $3 $4 $5

