# ============================================
# Saint Mary's Clinic - Patient Portal Lab
# Vulnerable Lab Environment (Educational Use)
# ============================================

FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# ----------------------------
# Install packages
# ----------------------------
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    php-mysql \
    libapache2-mod-php \
    mysql-server \
    openssh-server \
    build-essential \
    curl \
    nano \
    net-tools \
    && rm -rf /var/lib/apt/lists/*

# ----------------------------
# Configure SSH
# ----------------------------
RUN mkdir -p /var/run/sshd
RUN sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin no/' /etc/ssh/sshd_config
RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config
RUN echo "PermitRootLogin no" >> /etc/ssh/sshd_config

# ----------------------------
# Create dr_house user
# ----------------------------
RUN useradd -m -s /bin/bash dr_house && \
    echo "dr_house:house" | chpasswd

# ----------------------------
# Configure Apache to parse .php.jpg as PHP
# ----------------------------
RUN echo '<Directory /var/www/html/uploads>\n\
    Options -Indexes\n\
    <FilesMatch "\\.php\\.">\n\
        SetHandler application/x-httpd-php\n\
    </FilesMatch>\n\
</Directory>' > /etc/apache2/conf-available/uploads-php.conf && \
    a2enconf uploads-php

# ----------------------------
# Copy web application files
# ----------------------------
COPY src/ /var/www/html/
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod 777 /var/www/html/uploads

# Remove default Apache page
RUN rm -f /var/www/html/index.html

# ----------------------------
# Copy and initialize database schema
# ----------------------------
COPY db/schema.sql /docker-entrypoint-initdb.d/schema.sql

# ----------------------------
# Compile and install SUID binaries
# (Only health-check is vulnerable - others use full paths)
# ----------------------------
COPY binary/health-check.c /tmp/health-check.c
COPY binary/backup-manager.c /tmp/backup-manager.c
COPY binary/network-monitor.c /tmp/network-monitor.c

# Compile all three
RUN gcc -o /usr/bin/health-check /tmp/health-check.c && \
    gcc -o /usr/bin/backup-manager /tmp/backup-manager.c && \
    gcc -o /usr/bin/network-monitor /tmp/network-monitor.c && \
    chown root:root /usr/bin/health-check /usr/bin/backup-manager /usr/bin/network-monitor && \
    chmod 4755 /usr/bin/health-check /usr/bin/backup-manager /usr/bin/network-monitor && \
    rm /tmp/health-check.c /tmp/backup-manager.c /tmp/network-monitor.c

# ----------------------------
# Copy entrypoint script
# ----------------------------
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# ----------------------------
# Expose ports
# ----------------------------
EXPOSE 80 22

# ----------------------------
# Start services
# ----------------------------
ENTRYPOINT ["/entrypoint.sh"]
