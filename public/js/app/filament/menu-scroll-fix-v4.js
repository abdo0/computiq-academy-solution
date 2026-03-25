document.addEventListener('livewire:navigated', () => {
    let sidebar_item = document.querySelector('.fi-sidebar-item.fi-active');
    if (sidebar_item) {
        sidebar_item.scrollIntoView({ behavior: "instant", block: "center", inline: "center" });
    }
    setTimeout(() => {
        let sidebar_item = document.querySelector('.fi-sidebar-item.fi-active');
        if (sidebar_item) {
            sidebar_item.scrollIntoView({ behavior: "instant", block: "center", inline: "center" });
        }
    }, 0);
});
