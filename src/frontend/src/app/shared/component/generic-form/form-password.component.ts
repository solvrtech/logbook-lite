import { Component, EventEmitter, Input, Output } from '@angular/core';
import { BaseFormFieldComponent } from './form-field.base';

/**
 * Generic wrapper component for password input using reactive form driven technique.
 */
@Component({
  selector: 'app-form-password',
  templateUrl: './form-password.component.html',
})
export class FormPasswordComponent extends BaseFormFieldComponent {
  /** max input length allowed, if null then it will not be used/visible */
  @Input() hintMax: number | null = null;

  /** Emitted on blur event */
  @Output() blurEvent = new EventEmitter<any>();

  passwordVisible = false;

  togglePasswordVisibility() {
    this.passwordVisible = !this.passwordVisible;
  }
}
