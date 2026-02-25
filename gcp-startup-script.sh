#!/bin/bash
# Optimized Startup script for Hospital Portal Lab on GCP VM
# Designed for Vulnerability Simulation (SQLi -> RCE -> PrivEsc)
set -e

# --- 1. System Dependencies ---
echo "[*] Installing system dependencies..."
apt-get update
apt-get install -y apache2 php php-mysql mysql-server git openssh-server build-essential curl sudo cron

# --- 2. Repository Setup ---
REPO_URL="https://github.com/pratiyk/hospital-portal-lab.git"
REPO_DIR="/opt/hospital-portal-lab"

echo "[*] Cloning repository..."
rm -rf "$REPO_DIR"
git clone "$REPO_URL" "$REPO_DIR"
cd "$REPO_DIR"

# --- 3. Web Application Deployment ---
echo "[*] Deploying web application..."
rm -rf /var/www/html/*
cp -r src/* /var/www/html/
mkdir -p /var/www/html/uploads
chown -R www-data:www-data /var/www/html/
chmod 777 /var/www/html/uploads

# Configure Apache to parse .php.jpg as PHP in uploads directory
cat <<EOF > /etc/apache2/conf-available/uploads-php.conf
<Directory /var/www/html/uploads>
    Options -Indexes
    <FilesMatch "\\.php\\.">
        SetHandler application/x-httpd-php
    </FilesMatch>
</Directory>
EOF
a2enconf uploads-php
systemctl restart apache2

# --- 4. Database Setup ---
echo "[*] Initializing database..."
# Start MySQL
systemctl start mysql
# Wait for MySQL to be ready
until mysqladmin ping >/dev/null 2>&1; do echo "[.] Waiting for MySQL..."; sleep 2; done

# Import schema (Wipes and resets the lab database to fresh state)
echo "[*] Resetting database..."
mysql -u root -e "DROP DATABASE IF EXISTS hospital_portal;"
mysql -u root < db/schema.sql

# --- 5. User & SSH Setup ---
echo "[*] Configuring users and SSH..."
if ! id -u dr_house >/dev/null 2>&1; then
    useradd -m -s /bin/bash dr_house
    echo "dr_house:house" | chpasswd
fi

# Set up SSH directory and keys (Pattern match: leaked private key)
mkdir -p /home/dr_house/.ssh
ssh-keygen -t rsa -b 2048 -f /home/dr_house/.ssh/id_rsa -N ""
cp /home/dr_house/.ssh/id_rsa.pub /home/dr_house/.ssh/authorized_keys
chown -R dr_house:dr_house /home/dr_house/
chmod 700 /home/dr_house/.ssh
chmod 600 /home/dr_house/.ssh/authorized_keys

# LEAK: Copy private key to a publicly accessible but "hidden" location
mkdir -p /var/www/html/assets/backups
cp /home/dr_house/.ssh/id_rsa /var/www/html/assets/backups/id_rsa_backup
chmod 644 /var/www/html/assets/backups/id_rsa_backup

# Enable Password Authentication (GCP disables this by default)
sed -i 's/^PasswordAuthentication no/PasswordAuthentication yes/' /etc/ssh/sshd_config
sed -i 's/^#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config
if ! grep -q "^PasswordAuthentication yes" /etc/ssh/sshd_config; then
    echo "PasswordAuthentication yes" >> /etc/ssh/sshd_config
fi

systemctl restart ssh

# --- 6. Vulnerable "Monitored" App ---
# Created to give the systemd service something to run
echo "[*] Setting up monitored application..."
cat <<EOF > /usr/bin/hospital-monitor-app
#!/bin/bash
# Saint Mary's Clinic - Health Monitor Payload
# This script simulates a background monitoring process.
while true; do
    echo "[$(date)] System health check: OK" >> /var/log/hospital-monitor.log
    sleep 60
done
EOF
chmod +x /usr/bin/hospital-monitor-app

# --- 7. Systemd Service Misconfig (PrivEsc Path) ---
echo "[*] Setting up vulnerable systemd service..."

# A. Hospital Health Checker Service
cat <<EOF > /etc/systemd/system/hospital-monitor.service
[Unit]
Description=Saint Mary's Clinic Health Monitor
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/var/www/html
ExecStart=/usr/bin/hospital-monitor-app
Restart=always

[Install]
WantedBy=multi-user.target
EOF

# LAB CONFIG: Make the service file WRITABLE by www-data (The main exploit path)
chown www-data:www-data /etc/systemd/system/hospital-monitor.service

# --- 8. Sudoers Misconfig ---
# Allow www-data to restart the service without password
echo "www-data ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart hospital-monitor" > /etc/sudoers.d/hospital-lab
chmod 440 /etc/sudoers.d/hospital-lab

# --- 9. Cron Jobs & Success Flags ---
echo "[*] Finalizing setup and flags..."

# Flag Stage 2: Readable by www-data
echo "SUCCESS! You have gained a shell as www-data. Flag: VulnOS{W3b_Sh3ll_Acc3ss}" > /var/www/flag_stage2.txt
chown www-data:www-data /var/www/flag_stage2.txt
chmod 400 /var/www/flag_stage2.txt

# Flag Stage 3: Readable by root
echo "SUCCESS! You have achieved root access. Flag: VulnOS{R00t_Pr1v1l3g3_Escalation}" > /root/flag_stage3.txt
chmod 400 /root/flag_stage3.txt

# Cron job to restart the service every minute (triggers the student's payload)
echo "* * * * * root /usr/bin/systemctl restart hospital-monitor" > /etc/cron.d/hospital-lab-cron
chmod 644 /etc/cron.d/hospital-lab-cron

# --- 10. Start Services ---
systemctl daemon-reload
systemctl enable hospital-monitor
systemctl restart hospital-monitor

echo "Saint Mary's Clinic Hospital Portal Lab setup complete. Happy Hunting!"
