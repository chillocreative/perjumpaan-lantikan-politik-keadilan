import { ref } from 'vue';

export function useDragScroll() {
    const scrollEl = ref(null);
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    function onMouseDown(e) {
        if (!scrollEl.value) return;
        isDown = true;
        scrollEl.value.classList.add('!cursor-grabbing', 'select-none');
        startX = e.pageX - scrollEl.value.offsetLeft;
        scrollLeft = scrollEl.value.scrollLeft;
    }

    function onMouseLeave() {
        if (!scrollEl.value) return;
        isDown = false;
        scrollEl.value.classList.remove('!cursor-grabbing', 'select-none');
    }

    function onMouseUp() {
        if (!scrollEl.value) return;
        isDown = false;
        scrollEl.value.classList.remove('!cursor-grabbing', 'select-none');
    }

    function onMouseMove(e) {
        if (!isDown || !scrollEl.value) return;
        e.preventDefault();
        const x = e.pageX - scrollEl.value.offsetLeft;
        const walk = x - startX;
        scrollEl.value.scrollLeft = scrollLeft - walk;
    }

    return { scrollEl, onMouseDown, onMouseLeave, onMouseUp, onMouseMove };
}
