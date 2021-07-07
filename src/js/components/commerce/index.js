import Alpine from 'alpinejs'

export default class Commerce {
  constructor() {
    this.onBuyClick = this.buyClick.bind(this);
    this.onPickupChange = this.pickupChange.bind(this);
    this.onPickupdateChange = this.pickupdateChange.bind(this);

    this.init()
  }

  init() {
    window.Alpine = Alpine;
    Alpine.start();
    this.imports();
    this.addEvents();
  }

  imports() {
    if (document.querySelector('.js-toggle, .js-submit-on-change, .js-refresh-content')) import(/* webpackChunkName: "toggle-component" */ './toggle').then(({ default: Module }) => new Module());
    if (document.querySelector('.js-spinner')) import(/* webpackChunkName: "spinner-component" */ './spinner').then(({ default: Module }) => new Module());
    if (document.querySelector('.js-buy')) this.buyModule = import(/* webpackChunkName: "buy", webpackPrefetch: true */ './buy');
  }

  addEvents() {
    document.querySelectorAll('.js-buy').forEach(el => el.addEventListener('click', this.onBuyClick));

    const pickupSelect = document.querySelector('.js-pickup-select');
    const hiddenPickupSelect = document.querySelector('[type="hidden"][name="fields[pickupLocation][]"]');
    if (pickupSelect) {
      pickupSelect.addEventListener('change', this.onPickupChange);
    } else if (hiddenPickupSelect) {
      const savedDate = document.querySelector('[name="fields[pickupDate]"]');
      // Only trigger manual change event if there isn't a saved one
      if (savedDate && savedDate.value === '') {
        this.pickupChange({ currentTarget: { value: parseInt(hiddenPickupSelect.value, 10) } });
      }
    }

    const pickupdateSelect = document.querySelector('.js-pickupdate-select');
    if (pickupdateSelect) pickupdateSelect.addEventListener('change', this.onPickupdateChange);
  }

  buyClick(event) {
    event.preventDefault();
    const form = event.currentTarget.closest('form');
    if (form.isValid()) {
      this.buyModule.then(({ default: Module }) => new Module(event));
    }
  }

  pickupChange(event) {
    const pickupTimes = window.locationsPickupTimes[event.currentTarget.value];
    const pickupdateSelect = document.querySelector('.js-pickupdate-select');
    const pickuptimeSelect = document.querySelector('.js-pickuptime-select');

    if (typeof pickupTimes !== 'undefined') {
      pickupdateSelect.innerHTML = '';
      pickupTimes.forEach((el, i) => {
        const option = document.createElement('option');
        if (i === 0) {
          option.value = '';
          option.text = pickupTimes.length > 1 ? el.day : 'er zijn helaas geen ophaal data mogelijk';
        } else {
          option.value = el.day;
          const d = new Date(el.day);
          const dayFull = this.getDay(d).toLowerCase();
          option.text = `${dayFull} ${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
        }
        pickupdateSelect.append(option);
      });
    }

    if (pickuptimeSelect) pickuptimeSelect.innerHTML = '';
  }

  pickupdateChange(event) {
    const pickupSelect = document.querySelector('[name="fields[pickupLocation][]"]');
    if (pickupSelect) {
      const pickupTimes = window.locationsPickupTimes[pickupSelect.value];
      const pickuptimeSelect = document.querySelector('.js-pickuptime-select');

      if (typeof pickupTimes !== 'undefined') {
        pickuptimeSelect.innerHTML = '';
        const openingHours = pickupTimes.find(el => el.day === event.currentTarget.value);
        if (openingHours) {
          const closingTime = openingHours.pickupTimes.pop();
          openingHours.pickupTimes.forEach((el, i, arr) => {
            const option = document.createElement('option');
            if (i === 0) {
              option.value = '';
              option.text = pickupTimes.length > 1 ? el : 'er zijn helaas geen ophaal uren mogelijk';
            } else {
              const d = new Date(el);
              let dEnd;
              if ((i + 1) === arr.length) {
                dEnd = new Date(closingTime);
              } else {
                dEnd = new Date(el);
                dEnd.setHours(d.getHours() + 2);
              }
              const timeStart = `${`0${d.getHours()}`.slice(-2)}:${`0${d.getMinutes()}`.slice(-2)}`;
              const timeEnd = `${`0${dEnd.getHours()}`.slice(-2)}:${`0${dEnd.getMinutes()}`.slice(-2)}`;
              option.value = `${`0${d.getHours()}`.slice(-2)}:${`0${d.getMinutes()}`.slice(-2)}`;
              option.text = `${timeStart} - ${timeEnd}`;
            }
            pickuptimeSelect.append(option);
          });
        }
      }
    }
  }

  getDay(date) {
    switch (date.getDay()) {
      case 0: return 'Zondag';
      case 1: return 'Maandag';
      case 2: return 'Dinsdag';
      case 3: return 'Woensdag';
      case 4: return 'Donderdag';
      case 5: return 'Vrijdag';
      case 6: return 'Zaterdag';
      default: return '';
    }
  }
}
