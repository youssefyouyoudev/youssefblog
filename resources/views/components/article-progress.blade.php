<div class="fixed left-0 top-0 z-[70] h-1 w-full bg-transparent" aria-hidden="true">
    <div id="reading-progress" class="h-1 w-0 bg-brand shadow-[0_0_18px_rgba(57,255,136,.85)]"></div>
</div>

<script>
    document.addEventListener('scroll', () => {
        const progressBar = document.getElementById('reading-progress');
        if (!progressBar) return;

        const height = document.documentElement.scrollHeight - window.innerHeight;
        const progress = height > 0 ? (window.scrollY / height) * 100 : 0;
        progressBar.style.width = `${progress}%`;
    }, { passive: true });
</script>
