import { gsap } from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";

gsap.registerPlugin(ScrollToPlugin);

export default class NavPanel {
  constructor() {
    // Set Variables
    this.setProps();

    if (this.container) this.init();
  }

  setProps() {
    this.burgers = [...document.querySelectorAll('.js-hamburger')];
    this.container = document.querySelector('.js-nav-panel');
    this.state = 'closed';
    this.animPromise = new Promise(resolve => resolve());

    if (this.container) {
      this.inner = this.container.querySelector('.js-nav-panel-inner');
      this.menuItems = [...this.container.querySelectorAll('nav a:not(.js-accordion)')];
    }

    // Functions
    this.onMenuClick = this.menuClick.bind(this);
    this.onKeyUp = this.keyUp.bind(this);
    this.onMenuItemClick = this.menuItemClick.bind(this);
  }

  init() {
    if (this.burgers.length > 0) {
      this.addEvents();
    }
  }

  rebind() {
    this.removeEvents();
    this.setProps();
    this.init();
  }

  addEvents() {
    this.burgers.forEach(burger => burger.addEventListener('click', this.onMenuClick));
    window.addEventListener('keyup', this.onKeyUp);
    this.menuItems.forEach(anchor => anchor.addEventListener('click', this.onMenuItemClick));
  }

  removeEvents() {
    this.burgers.forEach(burger => burger.removeEventListener('click', this.onMenuClick));
    window.removeEventListener('keyup', this.onKeyUp);
    this.menuItems.forEach(anchor => anchor.removeEventListener('click', this.onMenuItemClick));
  }

  menuItemClick(event) {
    this.setActiveClass(event);
    this.burgers[0].click();
  }

  menuClick(event) {
    event.preventDefault();
    this.animPromise.pause; // eslint-disable-line no-unused-expressions

    this.burgers.forEach((burger) => {
      burger.classList.toggle('is-active');
      burger.classList.toggle('hamburger--white');
    });
    if (this.state === 'closed') {
      this.animPromise = this.open();
      return;
    }
    this.animPromise = this.close();
  }

  keyUp(event) {
    if (event.keyCode === 27 && this.state === 'open') this.burgers[0].click();
  }

  open() {
    this.state = 'open';
    this.resetInnerElements();

    this.container.classList.remove('pointer-events-none');

    const fullW = this.container.offsetWidth;
    this.container.removeAttribute('style');
    this.inner.style.width = `${fullW}px`;
    console.log(this.inner.offsetWidth);

    const tl = gsap.timeline();
    tl.to(window, {
      ease: 'expo.out',
      scrollTo: 0,
      duration: .55
    });
    tl.fromTo(this.container, {
      width: 0,
      opacity: 0,
    }, {
      ease: 'expo.out',
      duration: .55,
      width: this.inner.offsetWidth,
      opacity: 1,
      onComplete: () => {
        if (this.state === 'open') {
          this.inner.style.width = '100%';
        }
      }
    }, '-=.55');
    return tl;
  }

  close() {
    this.state = 'closed';

    this.container.classList.add('pointer-events-none');
    const fullW = this.container.offsetWidth;
    this.container.style.width = `${fullW}px`;
    this.inner.style.width = `${fullW}px`;

    window.scrollTo(0, 0);

    return gsap.to(this.container, {
      duration: .55,
      width: 0,
      opacity: 0,
      ease: 'expo.out',
      onComplete: () => {
        if (this.state === 'closed') {
          this.resetInnerElements();
        }
      }
    });
  }

  setActiveClass(event) {
    const org = this.container.querySelector('.is-active');
    if (org) org.classList.remove('is-active');
    event.currentTarget.classList.add('is-active');
  }

  resetInnerElements() {
    this.container.removeAttribute('style');
    this.inner.removeAttribute('style');
  }
}
