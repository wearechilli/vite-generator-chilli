import axios from 'axios';
import FormDataValidator from 'form-data-validator';
import isEmail from 'validator/lib/isEmail';
import equals from 'validator/lib/equals';
import * as Ladda from 'ladda';

export default class Validation {
  constructor() {
    // Variables
    this.forms = [...document.querySelectorAll('.js-validate')];
    this.successMessageWrapper = document.querySelector('.js-success-message');

    // Functions
    this.onSubmit = this.submitHandler.bind(this);

    this.init();
  }

  init() {
    this.validateForms();
    this.addEvents();
  }

  validateForms() {
    this.forms.forEach((form) => {
      const options = {
        scrollToFirstError: false,
        parentSelector: 'div',
        ignoreFields: ['g-recaptcha-response'],
        customTypes: [{
          type: 'email',
          rule: (field) => isEmail(field.value),
          message: 'dit is geen geldig e-mailadres'
        }],
        rules: [{
          field: '#newPassword',
          rule: (field) => equals(field.value, form.querySelector(field.dataset.equalTo).value)
        }]
      };

      FormDataValidator.validateForm(form, options);
    });
  }

  addEvents() {
    this.forms.forEach((form) => {
      form.addEventListener('submit', this.onSubmit);
    });
  }

  submitHandler(event) {
    const form = event.currentTarget;
    // const loader = Ladda.create(form.querySelector('[type="submit"'));

    if (form.isValid()) {
      event.preventDefault();
      this.submit(form);
    } else {
      // form is not valid
      event.preventDefault();
    }
  }

  submit(form, token = null) {
    // Regular Submit
    if (typeof form.dataset.ajax === 'undefined') {
      form.submit();
      return;
    }

    // Ajax submit
    const loader = Ladda.create(form.querySelector('[type="submit"]'));
    loader.start();

    const path = (form.getAttribute('action') != null && form.getAttribute('action') !== '')
      ? form.getAttribute('action') : '/';
    const formData = new FormData(form);
    if (token) formData.set('g-recaptcha-response', token);
    axios.post(path, formData, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    }).then((response) => {
      Ladda.stopAll();
      if (response.data.errors || !response.data.success) {
        if (response.data.spam) {
          alert('Het lijkt erop dat jouw bericht werd onderschept als spam. Indien dit probleem zich blijft voordoen, gelieve dan met ons contact op te nemen.'); // eslint-disable-line
        } else {
          this.showResponse({ form, isError: true, message: response.data.errors });
        }
      } else {
        this.showResponse({ form, isError: false, message: window.successMessage });
      }
    }).catch((err) => {
      console.log(err);
      alert('Er ging iets mis met het contactformulier. Gelieve met ons contact op te nemen.'); // eslint-disable-line
    });
  }

  showResponse(options = {}) {
    this.resetFields(options.form);
    const successBanner = this.successMessageWrapper.querySelector('div');

    if (options.isError) {
      successBanner.classList.add('border-red-500');
      successBanner.innerHTML = '';
      Object.keys(options.message).forEach((property) => {
        successBanner.innerHTML += `<p>${options.message[property]}</p>`;
      });
    }

    let h = 0;

    if (this.successMessageWrapper) {
      this.successMessageWrapper.classList.remove('h-0');
      h = this.successMessageWrapper.offsetHeight;
      this.successMessageWrapper.classList.add('h-0');
      this.successMessageWrapper.style.transition = 'height 0.35s ease-in-out';
      // Trigger reflow
      this.successMessageWrapper.offsetWidth; // eslint-disable-line no-unused-expressions
      this.successMessageWrapper.style.height = `${h}px`;
    }
  }

  resetFields(form) {
    [...form.querySelectorAll('input:not([type="hidden"]),select,textarea')].forEach(
      (field) => {
        field.value = '';
      }
    );
  }
}
