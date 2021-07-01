export default class ChilliLog {
  constructor() {
    this.init();
  }

  init() {
    this.log();
  }

  log() {
    if (/(chrome|firefox)/.test(navigator.userAgent.toLowerCase())) {
      let top = 'background-color: #ffffff; color: #020202;';

      if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {
        top += `padding: 20px 70px 16px 10px; background-image: url(${window.location.origin}/assets/img/log/logo.svg); background-position: 107px 9px; background-repeat: no-repeat; background-size: 40px 30px;`;
      }

      const bottom = 'padding: 20px 7px 16px 15px; border-left: 15px solid white; background-color: transparent; color: #020202;';

      const space = 'background-color: transparent;';

      const msg = '%c\n\n\n%cSpiced up by %c https://chilli.be %c\n\n\n';

      console.log(msg, space, top, bottom, space);
    } else if (window.console) {
      console.log('Spiced up by CHILLI - https://chilli.be');
    }
  }
}
