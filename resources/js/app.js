const backdropPointers = new Map();

document.addEventListener('pointerdown', event => {
    const target = event.target;
    backdropPointers.set(event.pointerId, {
        downTarget: target instanceof Element && target.matches('[role="dialog"]') ? target : null,
        upTarget: null,
    });
}, true);

document.addEventListener('pointerup', event => {
    const pointer = backdropPointers.get(event.pointerId);
    if (!pointer) {
        return;
    }

    const target = event.target;
    pointer.upTarget = target instanceof Element && target.matches('[role="dialog"]') ? target : null;
}, true);

document.addEventListener('pointercancel', event => {
    backdropPointers.delete(event.pointerId);
}, true);

document.addEventListener('click', event => {
    const target = event.target;
    const pointerId = typeof event.pointerId === 'number' ? event.pointerId : -1;
    const pointer = backdropPointers.get(pointerId);
    backdropPointers.delete(pointerId);

    if (! (target instanceof Element) || ! target.matches('[role="dialog"]')) {
        return;
    }

    if (pointer?.downTarget === target && pointer.upTarget === target) {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
}, true);

window.addEventListener('blur', () => {
    backdropPointers.clear();
});
