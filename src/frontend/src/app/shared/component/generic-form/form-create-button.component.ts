import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-create-button',
  templateUrl: './form-create-button.component.html',
})
export class FormCreateButtonComponent {
  @Input() disabled?: boolean;
  @Input() link?: string;
}
