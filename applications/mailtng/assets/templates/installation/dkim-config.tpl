## CONFIGURATION OPTIONS
PidFile 		/var/run/opendkim/opendkim.pid
Mode    		sv
Syslog  		yes
SyslogSuccess 		yes
UserID  		opendkim:opendkim
Socket  		inet:8891@127.0.0.1
Umask   		002
Canonicalization	relaxed/simple
Selector                default
KeyTable                refile:/etc/opendkim/KeyTable
SigningTable		refile:/etc/opendkim/SigningTable
ExternalIgnoreList	refile:/etc/opendkim/TrustedHosts
InternalHosts		refile:/etc/opendkim/TrustedHosts