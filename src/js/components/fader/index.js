export default class Fader {
  constructor() {
    // Set Variables
    this.containerItems = [...document.querySelectorAll('.fade-in')];

    // Functions
    this.onIsVisible = this.isVisible.bind(this);

    this.init();
  }

  init() {
    const observer = new IntersectionObserver(this.onIsVisible, { // eslint-disable-line
      threshold: 0.2
    });
    this.containerItems.forEach((item) => observer.observe(item));
  }

  isVisible(entries, observer) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        console.log('intersecting');
        entry.target.classList.add('transition-all');
        entry.target.classList.add('!opacity-100');
        entry.target.classList.add('!translate-y-0');
        observer.unobserve(entry.target);
      }
    });
  }
}
