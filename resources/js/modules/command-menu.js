/**
 * Command Menu (⌘K Palette) ESM Module
 * Implements keyboard navigation, dynamic search filtering, and safe lifecycle management.
 *
 * @module command-menu
 */

/**
 * Initializes the Command Menu component.
 *
 * @returns {void}
 */
export function initCommandMenu() {
  const backdrop = document.getElementById('cmdk-backdrop');
  const input = document.getElementById('cmdk-input');
  const empty = document.getElementById('cmdk-empty');
  const items = document.querySelectorAll('.cmdk-item');

  if (!backdrop || !input) return;

  let activeIndex = 0;

  /**
   * Opens the Command Palette modal and focuses search input.
   */
  const openCmdk = () => {
    backdrop.style.display = 'flex';
    backdrop.setAttribute('aria-hidden', 'false');
    if (input) {
      input.value = '';
      filterItems('');
      setTimeout(() => input.focus(), 50);
    }
  };

  /**
   * Closes the Command Palette modal.
   */
  const closeCmdk = () => {
    backdrop.style.display = 'none';
    backdrop.setAttribute('aria-hidden', 'true');
  };

  // Expose triggers globally for Blade buttons (e.g. search triggers)
  window.openCmdk = openCmdk;
  window.closeCmdk = closeCmdk;

  /**
   * Sets active highlighted item in command list.
   *
   * @param {number} index - Index to activate
   * @param {Element[]} [visibleList] - Optional array of currently visible elements
   */
  const setActiveIndex = (index, visibleList) => {
    const list = visibleList ?? Array.from(items).filter((i) => i.style.display !== 'none');
    items.forEach((i) => i.classList.remove('active'));

    if (list.length === 0) return;

    let targetIndex = index;
    if (targetIndex < 0) targetIndex = list.length - 1;
    if (targetIndex >= list.length) targetIndex = 0;
    activeIndex = targetIndex;

    const target = list[activeIndex];
    if (target) {
      target.classList.add('active');
      target.scrollIntoView({ block: 'nearest' });
    }
  };

  /**
   * Filters list items by search query string.
   *
   * @param {string} query - Filter term
   */
  const filterItems = (query) => {
    const q = query.toLowerCase().trim();
    let visibleCount = 0;
    const visibleItems = [];

    items.forEach((item) => {
      const searchData = item.getAttribute('data-search') ?? '';
      const text = `${searchData} ${item.textContent ?? ''}`;
      const isMatch = q === '' || text.toLowerCase().includes(q);

      item.style.display = isMatch ? 'flex' : 'none';
      if (isMatch) {
        visibleItems.push(item);
        visibleCount++;
      }
    });

    // Toggle group visibility based on matching children
    document.querySelectorAll('.cmdk-group').forEach((group) => {
      const hasVisible = Array.from(group.querySelectorAll('.cmdk-item')).some(
        (i) => i.style.display !== 'none'
      );
      group.style.display = hasVisible ? 'block' : 'none';
    });

    if (empty) {
      empty.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    setActiveIndex(0, visibleItems);
  };

  // Attach input listener
  input.addEventListener('input', (e) => {
    const target = e.target;
    filterItems(target?.value ?? '');
  });

  // Global Keyboard Shortcut: ⌘K or Ctrl+K
  document.addEventListener('keydown', (e) => {
    const isCmdK = (e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k';
    const isOpen = backdrop.style.display === 'flex';

    if (isCmdK) {
      e.preventDefault();
      isOpen ? closeCmdk() : openCmdk();
    } else if (isOpen) {
      if (e.key === 'Escape') {
        e.preventDefault();
        closeCmdk();
      } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        const visible = Array.from(items).filter((i) => i.style.display !== 'none');
        setActiveIndex(activeIndex + 1, visible);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const visible = Array.from(items).filter((i) => i.style.display !== 'none');
        setActiveIndex(activeIndex - 1, visible);
      } else if (e.key === 'Enter') {
        const visible = Array.from(items).filter((i) => i.style.display !== 'none');
        const activeItem = visible[activeIndex];
        if (activeItem) {
          e.preventDefault();
          activeItem.click();
          closeCmdk();
        }
      }
    }
  });

  // Backdrop click listener
  backdrop.addEventListener('click', (e) => {
    if (e.target === backdrop) closeCmdk();
  });
}
