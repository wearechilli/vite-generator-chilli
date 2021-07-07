export default class Spinner {
  constructor() {
    this.onInputSpinnerClick = this.inputSpinnerClick.bind(this);
    this.onInputSpinnerKeyUp = this.onInputSpinnerKeyUp.bind(this);

    this.init();
  }

  init() {
    this.initInputSpinners(document);
  }

  initInputSpinners(context) {
    const inputSpinners = context.querySelectorAll('.js-spinner');
    if (inputSpinners.length > 0) {
      inputSpinners.forEach((spinner) => {
        spinner.querySelectorAll('button').forEach(button => button.removeEventListener('click', this.onInputSpinnerClick));
        spinner.querySelectorAll('button').forEach(button => button.addEventListener('click', this.onInputSpinnerClick));

        const input = spinner.querySelector('input');
        if (input.classList.contains('js-refresh-on-change')) {
          input.addEventListener('change', this.onInputSpinnerClick);
          input.addEventListener('keyup', this.onInputSpinnerKeyUp);
        }
      });
    }
  }

  inputSpinnerClick(event) {
    event.preventDefault();
    const input = event.currentTarget.closest('.js-spinner').querySelector('input');
    if (input) {
      if (typeof event.currentTarget.dataset.increase !== 'undefined') this.increaseSpinner(input);
      if (typeof event.currentTarget.dataset.decrease !== 'undefined') this.decreaseSpinner(input);

      if (input.classList.contains('js-refresh-on-change')) this.submit(input);
    }
  }

  onInputSpinnerKeyUp(event) {
    event.preventDefault();
    if (event.keyCode === 13) {
      const input = event.currentTarget.closest('.js-spinner').querySelector('input');
      if (input.classList.contains('js-refresh-on-change')) this.submit(input);
    }
  }

  increaseSpinner(input) {
    if (input.hasAttribute('max') && parseFloat(input.value) >= parseFloat(input.max)) return;
    input.value = parseInt(parseFloat(input.value) + parseFloat(input.step), 10);
  }

  decreaseSpinner(input) {
    if (input.hasAttribute('min') && parseFloat(input.value) <= parseFloat(input.min)) return;
    input.value = parseInt(parseFloat(input.value) - parseFloat(input.step), 10);
  }

  submit(input) {
    const form = input.closest('form');
    const qtyInput = document.createElement('input');
    qtyInput.type = 'hidden';
    qtyInput.name = input.dataset.name;
    qtyInput.value = (parseFloat(input.value) / parseFloat(input.dataset.qtyInterval)).toFixed(0);
    form.append(qtyInput);
    form.submit();
  }
}
