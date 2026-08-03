import { postApi } from '../modules/api';

/**
 * Initializes language switching listeners on sidebar items.
 *
 * @returns {void}
 */
export function initLanguageSwitcher() {
    const languageItems = document.querySelectorAll('.language-select');
    if (languageItems.length === 0) return;

    languageItems.forEach((item) => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const selectedLang = item.getAttribute('data-lang');
            if (!selectedLang) return;

            const modalElement = document.getElementById('languageChangeModal');
            if (!modalElement) return;

            const modal = window.bootstrap?.Modal?.getOrCreateInstance(modalElement) 
                       ?? new window.bootstrap.Modal(modalElement);
            modal.show();

            const confirmBtn = document.getElementById('confirmChange');
            if (!confirmBtn) return;

            // Remove existing listener to prevent duplicate triggers
            const handleConfirm = async () => {
                confirmBtn.disabled = true;
                confirmBtn.setAttribute('data-loading', 'true');

                try {
                    modal.hide();
                    const result = await postApi('/admin/change-language', { lang: selectedLang });

                    if (result.success) {
                        window.location.reload();
                    } else {
                        console.error('[Language Switch Error]:', result.error);
                        alert(result.error ?? 'Failed to update language preference.');
                    }
                } catch (err) {
                    console.error('[Language Switch Exception]:', err);
                } finally {
                    confirmBtn.disabled = false;
                    confirmBtn.removeAttribute('data-loading');
                    confirmBtn.removeEventListener('click', handleConfirm);
                }
            };

            confirmBtn.addEventListener('click', handleConfirm, { once: true });
        });
    });
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLanguageSwitcher);
} else {
    initLanguageSwitcher();
}