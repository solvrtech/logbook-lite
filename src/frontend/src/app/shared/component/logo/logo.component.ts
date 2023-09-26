import { Component, Input } from '@angular/core';
import { AppLogo } from 'src/app/apps/interfaces/app.interface';
import { LogoService } from '../../services/logo/logo.service';

@Component({
  selector: 'app-logo',
  template: '',
})
export class LogoComponent {
  @Input() set logo(value: AppLogo | any) {
    const logo = value ? value : null;
    this.logoService.setLogo(logo);
  }

  constructor(private logoService: LogoService) {}
}
