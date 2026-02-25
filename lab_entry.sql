INSERT INTO labs (id, title, description, difficulty, category, estimated_time_mins, cover_image_url, is_standalone, gcp_image_name, requires_vm, points, content_details)
VALUES (
    89,
    'Saint Mary''s Clinic: Hospital Portal',
    'A realistic hospital portal simulation featuring a three-stage attack chain. Students will exploit SQL Injection to leak data, bypass file upload filters to gain a foothold, and leverage systemd service misconfigurations for root privilege escalation.',
    'Medium',
    'Web & Linux Exploitation',
    45,
    'https://vulnos.tech/images/labs/hospital_portal.png',
    true,
    'hospital-portal-v1',
    true,
    35,
    '{
      "chapters": [
        {
          "id": "1",
          "title": "Stage 1: SQL Injection",
          "markdown": "The clinic portal has a public appointment checker. An outdated \"SecureGuard\" filter tries to block SQL keywords, but is poorly implemented.\n\n**Goal:** Bypass the filter to exfiltrate the hidden stage 1 flag from the `secret_flags` table.",
          "question": "What is the flag found in the secret_flags table?",
          "flag_format": "VulnOS{...}",
          "flag_answer_for_validation": "VulnOS{SQLi_D4t4_Exf1l}",
          "hints": ["Try using MySQL versioned comments (/*!50000...*/) to bypass the filter."]
        },
        {
          "id": "2",
          "title": "Stage 2: File Upload Bypass",
          "markdown": "After gaining access to the staff portal, you discover a profile picture upload feature. The application claims to only allow .jpg files.\n\n**Goal:** Bypass the extension check and execute a PHP web shell. Find the second flag at `/var/www/flag_stage2.txt`.",
          "question": "Submit Flag 2 (User Access):",
          "flag_format": "VulnOS{...}",
          "flag_answer_for_validation": "VulnOS{W3b_Sh3ll_Acc3ss}",
          "hints": ["The server might be configured to parse files with multiple extensions. Try .php.jpg."]
        },
        {
          "id": "3",
          "title": "Stage 3: Privilege Escalation",
          "markdown": "You are now on the system as `www-data`. Enumerate the system to find a way to escalate your privileges to root. Look for custom services and check their file permissions.\n\n**Goal:** Compromise the root account and read the final flag at `/root/flag_stage3.txt`.",
          "question": "Submit Flag 3 (Root Access):",
          "flag_format": "VulnOS{...}",
          "flag_answer_for_validation": "VulnOS{R00t_Pr1v1l3g3_Escalation}",
          "hints": ["Check the permissions of files in /etc/systemd/system/. Is there any service you can modify?"]
        }
      ],
      "resources": [
        {
            "type": "link",
            "title": "Official Walkthrough",
            "url": "https://github.com/pratiyk/hospital-portal-lab/blob/main/walkthrough.md"
        }
      ]
    }'
);

-- Update Tags
UPDATE labs
SET tags = ARRAY['SQLi', 'File Upload', 'Privilege Escalation', 'Systemd']
WHERE id = 89;

-- Update Objectives
UPDATE labs
SET learning_objectives = 'Perform Union-based SQL injection with filter bypass.
        "Exploit server-side file upload misconfigurations.
        "Enumerate and hijack systemd service units for privilege escalation.
        "Navigate Linux filesystems as a low-privileged user.'
WHERE id = 89;
