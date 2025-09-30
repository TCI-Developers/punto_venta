// ===== API del loader =====
    const loader = (() => {
    const root = document.getElementById('loader');
    const bar = document.getElementById('loader-bar');
    const percent = document.getElementById('loader-percent');
    const label = document.getElementById('loader-label');

    let prevOverflowHtml = '';
    let prevOverflowBody = '';

function lockScroll(){
        prevOverflowHtml = document.documentElement.style.overflow;
        prevOverflowBody = document.body.style.overflow;
        document.documentElement.classList.add('is-locked');
        document.body.classList.add('is-locked');
}

function unlockScroll(){
        document.documentElement.classList.remove('is-locked');
        document.body.classList.remove('is-locked');
        document.documentElement.style.overflow = prevOverflowHtml;
        document.body.style.overflow = prevOverflowBody;
}

function show(){
        root.classList.add('is-visible');
        root.setAttribute('aria-hidden','false');
        lockScroll();
}

function hide(){
        root.classList.remove('is-visible');
        root.setAttribute('aria-hidden','true');
        unlockScroll();
}

function progress(value){
        const v = Math.max(0, Math.min(100, Number(value) || 0));
        bar.style.width = v + '%';
        percent.textContent = Math.round(v) + '%';
}

function text(msg){ label.textContent = msg; }
return { show, hide, progress, text };
})();

// ===== DEMO: simulación de carga =====
document.addEventListener('DOMContentLoaded', () => {
    // Muestra el loader y simula progreso hasta 100%
    loader.show();
    let p = 0;
    const tick = setInterval(() => {
        p += Math.random() * 17; // progreso aleatorio
        if (p >= 100) {
        p = 100;
        clearInterval(tick);
        loader.text('Listo');
        setTimeout(() => loader.hide(), 350);
        } else {
        loader.text('Cargando… ' + Math.round(p) + '%');
        }
        loader.progress(p);
    }, 300);
});