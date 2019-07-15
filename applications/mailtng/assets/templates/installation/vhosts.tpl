<VirtualHost *:80>
    DocumentRoot "/var/mailtng"
    <Directory /var/mailtng>
        AllowOverride all
        Options Indexes FollowSymLinks ExecCGI
        AddHandler cgi-script .cgi .pl
        Order Deny,Allow
        $P{ALL}
    </Directory>
</VirtualHost>