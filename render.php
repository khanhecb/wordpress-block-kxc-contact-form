<?php 
use KXC\ContactForm\Api;

defined('ABSPATH') || exit;


$rest_url = esc_url(Api::get_rest_url());

?>

<form id="kxc-contact-form" class="kxc-contact-form" method="post" action="<?php echo $rest_url ?>">

    <div style="display:none !important;" aria-hidden="true">
        <input type="text" name="website_kxc" tabindex="-1" autocomplete="off">
    </div>

    <!-- User name -->
    <input
        type="text"
        id="contact-user-name"
        name="name"
        class="kxc-contact-form__input"
        placeholder="Họ và tên"
        required
        maxlength="64"
        autocomplete="on"
    >


    <!-- Phone number -->
    <input
        type="tel"
        id="contact-user-phone"
        name="phone"
        class="kxc-contact-form__input"
        placeholder="Số điện thoại"
        required
        maxlength="13"
        autocomplete="on"
    >

    <!-- Message -->
    <textarea
        id="contact-user-message"
        name="message"
        class="kxc-contact-form__textarea"
        rows="4"
        placeholder="Bạn có bất cứ vấn đề gì hãy nhắn tin ở đây cho chúng tôi"
        required
        maxlength="512"
    ></textarea>

    <!-- Submit -->
    <button type="submit" class="kxc-contact-form__submit">
        Gửi liên hệ
    </button>

    <!-- Status -->
    <div class="kxc-contact-message" aria-live="polite"></div>
</form>