@props([
    'value' => 0,
    'prefix' => '',
    'suffix' => '',
    'decimals' => 2,
    'duration' => 800,
    'class' => '',
])

<span {{ $attributes->merge(['class' => 'number-flow-wrapper ' . $class]) }}
      data-number-flow
      data-value="{{ $value }}"
      data-prefix="{{ $prefix }}"
      data-suffix="{{ $suffix }}"
      data-decimals="{{ $decimals }}"
      data-duration="{{ $duration }}">
    <span class="number-flow-prefix">{{ $prefix }}</span><span class="number-flow-digits">{{ number_format((float)$value, (int)$decimals) }}</span><span class="number-flow-suffix">{{ $suffix }}</span>
</span>

@once
<style>
.number-flow-wrapper {
    display: inline-flex;
    align-items: center;
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
    transition: color 0.25s ease, transform 0.25s ease;
}

.number-flow-wrapper.is-updating {
    transform: scale(1.05);
}

.number-flow-digit {
    display: inline-block;
    position: relative;
    overflow: hidden;
    height: 1.15em;
    vertical-align: bottom;
}

.number-flow-column {
    display: flex;
    flex-direction: column;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.number-flow-num {
    height: 1.15em;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
(function () {
    function animateNumber(el, startVal, endVal, duration, prefix, suffix, decimals) {
        const startTime = performance.now();
        const diff = endVal - startVal;

        el.classList.add('is-updating');

        function update(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out cubic
            const ease = 1 - Math.pow(1 - progress, 3);
            const current = startVal + (diff * ease);

            const digitsEl = el.querySelector('.number-flow-digits');
            if (digitsEl) {
                digitsEl.textContent = current.toLocaleString(undefined, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                el.classList.remove('is-updating');
                el.dataset.value = endVal;
            }
        }

        requestAnimationFrame(update);
    }

    function initNumberFlow(el) {
        if (el._nfObserved) return;
        el._nfObserved = true;

        let prevVal = parseFloat(el.dataset.value || '0');

        const observer = new MutationObserver(() => {
            const newVal = parseFloat(el.dataset.value || '0');
            const duration = parseInt(el.dataset.duration || '800', 10);
            const prefix = el.dataset.prefix || '';
            const suffix = el.dataset.suffix || '';
            const decimals = parseInt(el.dataset.decimals || '2', 10);

            if (!isNaN(newVal) && newVal !== prevVal) {
                animateNumber(el, prevVal, newVal, duration, prefix, suffix, decimals);
                prevVal = newVal;
            }
        });

        observer.observe(el, { attributes: true, attributeFilter: ['data-value'] });
    }

    // Auto-init all components
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-number-flow]').forEach(initNumberFlow);
    });

    // Livewire support for dynamic DOM updates
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            if (el && el.querySelectorAll) {
                el.querySelectorAll('[data-number-flow]').forEach(initNumberFlow);
            }
        });
    });

    window.animateNumberFlow = function(selectorOrEl, newValue) {
        const el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl;
        if (el && el.dataset) {
            el.dataset.value = newValue;
        }
    };
})();
</script>
@endonce
