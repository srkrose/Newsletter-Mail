# Newsletter-Mail
Daily newsletter mail sending PHP script using GNews API and PHPMailer for cPanel

### 1. Create Folder and Add Files

Create a folder named `scripts` in cPanel and add the files from the repository to the created folder

### 2. Replace Required Fields

Replace the required fields in the following files:

- /config/.smtpconfig: `Replace with the required SMTP settings of the sender email`
- /news/gnnews.php: `Replace the required field with your GNews API key`
- /news/recipient_list.txt: `Add the required recipient email addresses`

### 3. Set up Cron Job

Set up a cron job to run the script at your desired frequency

```
X X X X X /usr/local/bin/php /home/sample/scripts/news/gnnews.php
```

### Reference:

- https://github.com/PHPMailer/PHPMailer
- https://gnews.io/
