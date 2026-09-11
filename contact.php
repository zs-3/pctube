<?php
// contact.php

$pageTitle = "Contact Us - PISSCAT";
$metaDescription = "Get in touch with PISSCAT administration or submit DMCA takedown notices.";

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 900px; margin: 0 auto; background: #1a1a1a; padding: 35px; border-radius: 10px; border: 1px solid #282828;">
    <h1 style="color: #ffb703; font-size: 28px; margin-bottom: 20px; border-bottom: 2px solid #282828; padding-bottom: 12px;">
        Contact Us
    </h1>

    <p style="color: #ccc; line-height: 1.7; margin-bottom: 30px;">
        Have questions, feedback, partnership requests, or compliance inquiries? Get in touch with the PISSCAT team directly via email below.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: #111; padding: 25px; border-radius: 8px; border: 1px solid #282828;">
            <div style="font-size: 24px; color: #ffb703; margin-bottom: 10px;"><i class="fa-solid fa-envelope"></i></div>
            <h3 style="color: #fff; font-size: 18px; margin-bottom: 8px;">General & Support</h3>
            <p style="color: #aaa; font-size: 14px; margin-bottom: 12px;">For general questions, site issues, advertising opportunities, or feedback:</p>
            <a href="mailto:admin@pisscat.com" style="color: #ffb703; font-weight: bold; font-size: 16px;">admin@pisscat.com</a>
        </div>

        <div style="background: #111; padding: 25px; border-radius: 8px; border: 1px solid #282828;">
            <div style="font-size: 24px; color: #ffb703; margin-bottom: 10px;"><i class="fa-solid fa-shield-halved"></i></div>
            <h3 style="color: #fff; font-size: 18px; margin-bottom: 8px;">DMCA & Copyright</h3>
            <p style="color: #aaa; font-size: 14px; margin-bottom: 12px;">For copyright infringement notices or removal requests:</p>
            <a href="mailto:dmca@pisscat.com" style="color: #ffb703; font-weight: bold; font-size: 16px;">dmca@pisscat.com</a>
        </div>
    </div>

    <div style="background: #221e10; border: 1px solid #ffb703; border-radius: 6px; padding: 20px; color: #ccc; font-size: 14px; line-height: 1.6;">
        <strong style="color: #ffb703;">Important Note for DMCA Requests:</strong> Please refer to our <a href="dmca.php" style="color: #ffb703; text-decoration: underline;">DMCA Takedown Policy</a> for all required details to ensure your notice is processed without delay.
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
