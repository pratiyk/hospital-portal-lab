#!/bin/bash
# ============================================
# Saint Mary's Clinic - Lab Entrypoint Script
# ============================================

set -e

echo "[*] Starting Saint Mary's Clinic Patient Portal Lab..."

# ----------------------------
# Start MySQL
# ----------------------------
echo "[*] Starting MySQL server..."
service mysql start

# Wait for MySQL to be ready
echo "[*] Waiting for MySQL to initialize..."
for i in $(seq 1 30); do
    if mysqladmin ping --silent 2>/dev/null; then
        echo "[+] MySQL is ready."
        break
    fi
    sleep 1
done

# ----------------------------
# Initialize Database
# ----------------------------
echo "[*] Initializing database..."
mysql -u root < /docker-entrypoint-initdb.d/schema.sql 2>/dev/null || echo "[!] Database may already be initialized."

# ----------------------------
# Start SSH
# ----------------------------
echo "[*] Starting SSH server..."
service ssh start

# ----------------------------
# Set permissions
# ----------------------------
echo "[*] Setting file permissions..."
chown -R www-data:www-data /var/www/html/uploads
chmod 777 /var/www/html/uploads

# ----------------------------
# Create success message files
# ----------------------------
# Stage 2: Only readable after getting a shell as www-data
echo "SUCCESS! You have gained a shell as www-data through the file upload bypass. Flag: VulnOS{W3b_Sh3ll_Acc3ss}" > /var/www/flag_stage2.txt
chown www-data:www-data /var/www/flag_stage2.txt
chmod 400 /var/www/flag_stage2.txt

# Stage 3: Only readable as root
echo "SUCCESS! You have achieved root access. The lab is fully compromised. Flag: VulnOS{R00t_Pr1v1l3g3_Escalation}" > /root/flag_stage3.txt
chmod 400 /root/flag_stage3.txt

# ----------------------------
# Start Apache (foreground)
# ----------------------------
echo "[+] All services started successfully!"
echo "[+] ============================================"
echo "[+]  Saint Mary's Clinic Patient Portal"
echo "[+]  Web:  http://localhost:80"
echo "[+]  SSH:  ssh dr_house@localhost -p 22"
echo "[+] ============================================"
echo "[*] Starting Apache in foreground..."
exec apachectl -D FOREGROUND
