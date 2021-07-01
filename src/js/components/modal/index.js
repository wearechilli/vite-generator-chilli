import axios from 'axios';
import * as Ladda from 'ladda';

export default class Modal {
  constructor(config = {}) {
    // Variables
    this.config = Object.assign({
      // ... defaults
    }, config);
    this.app = document.querySelector('.app');
    this.body = document.querySelector('body');

    // Callbacks
    this.onKeyUp = this.keyUp.bind(this);
    this.onCloseClick = this.closeClick.bind(this);
    this.onBackdropClick = this.backdropClick.bind(this);

    return this.init();
  }

  init() {
    return this.load(this.config.url).then((response) => {
      this.show(response);
    });
  }

  unBlur() {
    document.querySelectorAll('body, html').forEach((el) => {
      el.classList.remove('h-full');
    });

    const y = this.app.style.top.substring(0, this.app.style.top.length - 2) * -1;
    this.app.removeAttribute('style');
    document.documentElement.classList.remove('scroll-smooth');
    window.scrollTo(0, y);
    document.documentElement.classList.add('scroll-smooth');

    this.body.classList.remove('overflow-y-scroll');
    this.body.classList.remove('blurred');
  }

  blur() {
    document.querySelectorAll('body, html').forEach((el) => {
      el.classList.add('h-full');
    });

    const y = this.app.getBoundingClientRect().top;
    this.app.style.cssText = `position: fixed; width: 100%;/*filter: blur(.08em); */top: ${y}px;`;

    this.body.classList.add('overflow-y-scroll');
    this.body.classList.add('blurred');
  }

  load(url) {
    return axios.get(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
  }

  show(response) {
    Ladda.stopAll();

    this.blur();

    const div = document.createElement('div');
    div.innerHTML = response.data;
    this.modal = document.body.appendChild(div);
    this.innerModal = this.modal.querySelector('.js-modal');
    this.backdrop = this.modal.querySelector('.js-backdrop');
    this.innerModal.offsetHeight; // eslint-disable-line no-unused-expressions
    this.innerModal.classList.toggle('opacity-0');
    this.backdrop.classList.toggle('opacity-0');

    window.scrollTo(0, 0);

    this.addEvents();
    this.cmScript();
  }

  close() {
    this.unBlur();

    document.body.removeChild(this.modal);

    this.removeEvents();
  }

  addEvents() {
    window.addEventListener('keyup', this.onKeyUp);
    this.modal.querySelectorAll('.js-close').forEach(el => el.addEventListener('click', this.onCloseClick));
    this.modal.querySelector('.js-backdrop').addEventListener('click', this.onBackdropClick);
  }

  removeEvents() {
    window.removeEventListener('keyup', this.onKeyUp);
  }

  keyUp(event) {
    if (event.keyCode === 27) this.close();
  }

  closeClick() {
    this.close();
  }

  backdropClick() {
    this.close();
  }

  cmScript() {
    if (this.config.url === '/ajax/modals/newsletter') {
      // Inject Google Maps JavaScript api script tag
      const jsFile = document.createElement('script');
      jsFile.type = 'text/javascript';
      jsFile.src = 'https://js.createsend1.com/javascript/copypastesubscribeformlogic.js';
      document.getElementsByTagName('head')[0].appendChild(jsFile);
    }
  }
}
