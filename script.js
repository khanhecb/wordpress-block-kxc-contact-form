document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('kxc-contact-form');
    if (!form) return;

    if (form.dataset.kxcBound === '1') return;
    form.dataset.kxcBound = '1';

    const messageBox = form.querySelector('.kxc-contact-message');
    const submitBtn  = form.querySelector('.kxc-contact-form__submit');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (submitBtn.disabled) return;

        // Reset State
        submitBtn.disabled = true;
        messageBox.className = 'kxc-contact-message';
        messageBox.textContent = 'Đang gửi...';

        const apiUrl   = form.action;
        const formData = new FormData(form);
        
        // Convert FormData sang Plain Object để gửi JSON
        const payload  = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            // Lấy response dưới dạng text trước để chống crash JSON.parse() khi server trả HTML lỗi
            const responseText = await response.text();
            let result;

            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Server response không phải JSON:', responseText);
                throw new Error('Máy chủ phản hồi không đúng định dạng JSON.');
            }

            // Xử lý Response
            if (response.ok && result.success) {
                messageBox.className = 'kxc-contact-message success'; 
                messageBox.innerHTML = result.message || 'Gửi thông tin thành công!';
                form.reset();
            } else {
                messageBox.className = 'kxc-contact-message error';
                
                // Trường hợp WP_Error trả về mảng thông báo hoặc chuỗi lỗi
                const errorMsg = Array.isArray(result.message)
                    ? result.message.join('<br>')
                    : (result.message || result.code || 'Có lỗi xảy ra, vui lòng thử lại.');
                    
                messageBox.innerHTML = errorMsg;
            }

        } catch (error) {
            console.error('Submit Error:', error);
            messageBox.className = 'kxc-contact-message error';
            messageBox.textContent = 'Không thể kết nối tới máy chủ.';
        } finally {
            submitBtn.disabled = false;
        }
    });
});