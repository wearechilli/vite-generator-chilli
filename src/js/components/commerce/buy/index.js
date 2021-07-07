import axios from 'axios';
import * as Ladda from 'ladda';
import Modal from '../../modal';

export default class Buy {
  constructor(event) {
    try {
      this.form = event.currentTarget.closest('form');
      this.action = this.form.getAttribute('action');
      this.formData = new FormData(this.form);
      this.formData.set(event.currentTarget.getAttribute('name'), event.currentTarget.getAttribute('value'));
      this.formData.set('qty', (parseFloat(this.formData.get('frontend-qty')) / parseFloat(this.form.querySelector('[name="frontend-qty"]').dataset.qtyInterval)).toFixed(0));
      this.submit();
    } catch {
      // If we fail, just submit the form
      event.currentTarget.closest('form').submit();
    }
  }

  submit() {
    const loader = Ladda.create(this.form.querySelector('[type="submit"]'));
    loader.start();

    axios.post(this.action, this.formData, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    }).then((response) => {
      // Update cart icon
      // const { totalQty } = response.data.cart;
      // axios.get(`/ajax/cartIcon?totalQty=${totalQty}`, {
      //   headers: { 'X-Requested-With': 'XMLHttpRequest' },
      // }).then((modal) => {
      //   document.querySelectorAll('.js-cart-icon').forEach((cartIcon) => {
      //     cartIcon.innerHTML = modal.data;
      //   });
      // });

      new Modal({
        url: `${window.location.origin}/ajax/commerce/modals/bought?purchasableId=${this.formData.get('purchasableId')}`,
        response: response.data,
        affectedLineItem: this.formData.get('purchasableId')
      }).then(() => {
        Ladda.stopAll();
      });
    });
  }
}
