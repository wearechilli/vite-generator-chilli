import axios from 'axios';

export default class Toggle {
  constructor() {
    this.init();
    this.submitOnChange();
    this.refreshContent();
  }

  init() {
    const toggles = document.querySelectorAll('.js-toggle');

    toggles.forEach((radio) => {
      const target = document.querySelector(radio.dataset.target);
      if (target && target.classList.contains('hidden')) this.removeAddressValidation(target);
    });

    document.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach((input) => {
      input.addEventListener('change', () => {
        // const { currentTarget } = event;
        toggles.forEach((radio) => {
          const target = document.querySelector(radio.dataset.target);
          const { invert } = radio.dataset;
          if (target) {
            if ((radio.checked && typeof invert === 'undefined')
              || (!radio.checked && invert)) {
              target.classList.remove('hidden');
              this.addAddressValidation(target);
            } else {
              target.classList.add('hidden');
              this.removeAddressValidation(target);
            }
          }
        });
      });
    });
  }

  removeAddressValidation(target) {
    target.querySelectorAll('input,select').forEach((el) => {
      if (el.required) {
        el.removeAttribute('required');
        el.dataset.required = true;
      }

      if (target.classList.contains('js-business-body')) el.value = '';
    });
  }

  addAddressValidation(target) {
    target.querySelectorAll('input,select').forEach((el) => {
      if (el.dataset.required) {
        el.setAttribute('required', true);
      }
    });
  }

  submitOnChange() {
    document.querySelectorAll('.js-submit-on-change').forEach((input) => {
      input.addEventListener('change', () => {
        input.closest('form').submit();
      });
    });
  }

  refreshContent() {
    const callback = (event) => {
      event.preventDefault();

      const el = event.currentTarget;
      const contentAnim = document.querySelector(el.dataset.placeholder);
      const content = contentAnim.querySelector('.js-refresh-content-placeholder');
      if (content && contentAnim) {
        contentAnim.classList.add('opacity-25');

        const promises = [];

        if (el.nodeName === 'INPUT' || el.nodeName === 'SELECT') {
          const form = el.closest('form');
          const path = (form.getAttribute('action') != null && form.getAttribute('action') !== '')
            ? form.getAttribute('action') : '/';
          const formData = new FormData(form);
          promises.push(axios.post(path, formData, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          }));
        }

        if (el.nodeName === 'A') {
          [...document.querySelectorAll('.js-refresh-content')].filter(a => a !== el).forEach((b) => {
            b.classList.remove('is-active');
          });
          el.classList.add('is-active');
        }

        Promise.all(promises).then(() => {
          axios.get(el.dataset.fetch, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          }).then((response) => {
            content.innerHTML = response.data;
            contentAnim.classList.remove('opacity-25');
          });
        });
      }
    };

    document.querySelectorAll('.js-refresh-content').forEach((el) => {
      if (el.nodeName === 'INPUT' || el.nodeName === 'SELECT') {
        el.addEventListener('change', callback.bind(this));
      } else if (el.nodeName === 'A') {
        el.addEventListener('click', callback.bind(this));
      }
    });
  }
}
