@props([
    'items' => [],
    'itemKey' => 'id',
    'wireOrderAction' => null,
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'sortable-container ' . $class]) }}
     data-sortable-list
     @if($wireOrderAction) data-wire-action="{{ $wireOrderAction }}" @endif>
    <ul class="sortable-list list-group list-group-flush border-0">
        {{ $slot }}
    </ul>
</div>

@once
<style>
.sortable-container {
    user-select: none;
}

.sortable-item {
    transition: transform 0.18s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.18s cubic-bezier(0, 0, 0.2, 1);
    cursor: default;
    will-change: transform;
}

.sortable-item.is-dragging {
    opacity: 0.85;
    background: #f8fafc !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    border-color: #6366f1 !important;
    z-index: 100;
    will-change: transform, opacity;
}

.sortable-handle {
    cursor: grab;
    color: #94a3b8;
    padding: 6px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s, background 0.15s;
}

.sortable-handle:hover {
    color: #475569;
    background: #f1f5f9;
}

.sortable-handle:active {
    cursor: grabbing;
}
</style>

<script>
(function () {
    let draggedItem = null;

    function initSortableList(container) {
        if (container._sortableInit) return;
        container._sortableInit = true;

        const list = container.querySelector('.sortable-list');
        if (!list) return;

        list.addEventListener('dragstart', (e) => {
            const item = e.target.closest('.sortable-item');
            if (!item) return;

            draggedItem = item;
            item.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.dataset.id || '');
        });

        list.addEventListener('dragend', (e) => {
            const item = e.target.closest('.sortable-item');
            if (item) {
                item.classList.remove('is-dragging');
            }
            draggedItem = null;
            dispatchNewOrder(container);
        });

        list.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const afterElement = getDragAfterElement(list, e.clientY);
            if (!draggedItem) return;

            if (afterElement == null) {
                list.appendChild(draggedItem);
            } else {
                list.insertBefore(draggedItem, afterElement);
            }
        });
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.sortable-item:not(.is-dragging)')];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function dispatchNewOrder(container) {
        const items = [...container.querySelectorAll('.sortable-item')];
        const newOrder = items.map((item, index) => ({
            id: item.dataset.id,
            order: index + 1
        }));

        // Dispatch custom JS event
        container.dispatchEvent(new CustomEvent('sorted', {
            detail: { order: newOrder, ids: newOrder.map(i => i.id) },
            bubbles: true
        }));

        // Trigger Livewire action if provided
        const wireAction = container.dataset.wireAction;
        if (wireAction && window.Livewire) {
            const component = Livewire.find(container.closest('[wire\\:id]')?.getAttribute('wire:id'));
            if (component) {
                component.call(wireAction, newOrder.map(i => i.id));
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-sortable-list]').forEach(initSortableList);
    });

    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            if (el && el.querySelectorAll) {
                el.querySelectorAll('[data-sortable-list]').forEach(initSortableList);
            }
        });
    });
})();
</script>
@endonce
