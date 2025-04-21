// filepath: ICT-Corps-Members-Hub/ICT-Corps-Members-Hub/public/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // Function to filter members based on input
    const filterMembers = () => {
        const input = document.getElementById('memberFilter').value.toLowerCase();
        const memberCards = document.querySelectorAll('.member-card');

        memberCards.forEach(card => {
            const name = card.querySelector('.member-name').textContent.toLowerCase();
            if (name.includes(input)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    };

    // Event listener for the filter input
    const filterInput = document.getElementById('memberFilter');
    if (filterInput) {
        filterInput.addEventListener('input', filterMembers);
    }

    // Example AJAX call to fetch members (to be implemented)
    const fetchMembers = async () => {
        try {
            const response = await fetch('/api/members');
            const members = await response.json();
            // Code to update the member list dynamically
        } catch (error) {
            console.error('Error fetching members:', error);
        }
    };

    // Initial fetch of members
    fetchMembers();
});

// Enhanced particles background for hero section
window.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('particles-bg');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let w = canvas.width = window.innerWidth;
    let h = canvas.height = window.innerHeight;
    let colors = ['#22c55e', '#bbf7d0', '#4ade80', '#16a34a'];
    let particles = Array.from({length: 80}, () => ({
        x: Math.random() * w,
        y: Math.random() * h,
        r: 1.5 + Math.random() * 2.5,
        dx: -0.7 + Math.random() * 1.4,
        dy: -0.7 + Math.random() * 1.4,
        o: 0.18 + Math.random() * 0.45,
        color: colors[Math.floor(Math.random() * colors.length)]
    }));
    function draw() {
        ctx.clearRect(0, 0, w, h);
        for (let p of particles) {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, 2 * Math.PI);
            ctx.fillStyle = p.color + Math.floor(p.o * 255).toString(16).padStart(2, '0');
            ctx.globalAlpha = p.o;
            ctx.shadowColor = p.color;
            ctx.shadowBlur = 12;
            ctx.fill();
            ctx.globalAlpha = 1;
            p.x += p.dx;
            p.y += p.dy;
            if (p.x < 0 || p.x > w) p.dx *= -1;
            if (p.y < 0 || p.y > h) p.dy *= -1;
        }
        requestAnimationFrame(draw);
    }
    draw();
    window.addEventListener('resize', () => {
        w = canvas.width = window.innerWidth;
        h = canvas.height = window.innerHeight;
    });
});
