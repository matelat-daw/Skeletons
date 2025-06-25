// const nav = document.querySelector('nav');
// const menuToggle = document.getElementById('menu-toggle');
// const menu = document.getElementById('menu');
// const links = menu.querySelectorAll('a');
// const header = document.querySelector('header');
// const aboutSection = document.getElementById('about');
// const contactSection = document.getElementById('contact');

// // Menú hamburguesa
// menuToggle.addEventListener('click', () => {
//     menu.classList.toggle('open');
// });

// // Navegación suave y control de menú fijo
// links.forEach(link => {
//     link.addEventListener('click', (e) => {
//         e.preventDefault();
//         menu.classList.remove('open');
//         const targetId = link.getAttribute('href').replace('#', '');
//         if (targetId === 'home') {
//             // Volver al inicio: mostrar header y nav normal
//             window.scrollTo({ top: 0, behavior: 'smooth' });
//             nav.classList.remove('fixed-nav');
//             document.body.style.paddingTop = '0';
//         } else {
//             // Navegar a la sección correspondiente
//             const section = document.getElementById(targetId);
//             const navHeight = nav.offsetHeight;
//             const sectionTop = section.getBoundingClientRect().top + window.scrollY;
//             // Si nav está fijo, restar su altura
//             let offset = sectionTop - (nav.classList.contains('fixed-nav') ? navHeight : 0);
//             // Si nav no está fijo pero va a fijarse, restar su altura
//             if (!nav.classList.contains('fixed-nav') && sectionTop > header.offsetHeight) {
//                 offset -= navHeight;
//             }
//             window.scrollTo({ top: offset, behavior: 'smooth' });
//         }
//     });
// });

// // Fijar nav al hacer scroll a about/contact
// window.addEventListener('scroll', () => {
//     const navHeight = nav.offsetHeight;
//     const headerBottom = header.getBoundingClientRect().bottom;
//     // Si el header está visible, nav normal
//     if (headerBottom > navHeight) {
//         nav.classList.remove('fixed-nav');
//         document.body.style.paddingTop = '0';
//     } else {
//         nav.classList.add('fixed-nav');
//         document.body.style.paddingTop = nav.offsetHeight + 'px';
//     }
// });

const nav = document.querySelector('nav');
const menuToggle = document.getElementById('menu-toggle');
const menu = document.getElementById('menu');
const links = menu.querySelectorAll('a');
const header = document.querySelector('header');

// Menú hamburguesa
menuToggle.addEventListener('click', () => {
    menu.classList.toggle('open');
});

// Navegación suave y control de menú fijo
links.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        menu.classList.remove('open');
        const targetId = link.getAttribute('href').slice(1);
        if (targetId === 'home') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            nav.classList.remove('fixed-nav');
            document.body.style.paddingTop = '0';
        } else {
            const section = document.getElementById(targetId);
            const navHeight = nav.offsetHeight;
            let sectionTop = section.getBoundingClientRect().top + window.scrollY;
            // Ajusta el scroll para que la sección no quede tapada por el nav fijo
            if (window.scrollY + navHeight < sectionTop) {
                sectionTop -= navHeight;
            }
            window.scrollTo({ top: sectionTop, behavior: 'smooth' });
        }
    });
});

// Fijar nav al hacer scroll
window.addEventListener('scroll', () => {
    const navHeight = nav.offsetHeight;
    const headerBottom = header.getBoundingClientRect().bottom;
    if (headerBottom > navHeight) {
        nav.classList.remove('fixed-nav');
        document.body.style.paddingTop = '0';
    } else {
        nav.classList.add('fixed-nav');
        document.body.style.paddingTop = nav.offsetHeight + 'px';
    }
});