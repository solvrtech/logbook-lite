import { Component, EventEmitter, Input, Output } from '@angular/core';
import { BaseFormFieldComponent } from './form-field.base';

/**
 * Generic wrapper component for <input>'s mat-input using reactive form driven technique.
 */
@Component({
  selector: 'app-form-input',
  templateUrl: './form-input.component.html',
})
export class FormInputComponent extends BaseFormFieldComponent {
  /** HTML input types (text, number, email, and so on) */
  @Input() type = '';

  /** max input length allowed, if null then it will not be used/visible */
  @Input() hintMax: number | null = null;

  @Input() min: number | null = null;
  @Input() max: number | null = null;

  /** if true and hintMax is not null, then a cut button will be visible to trim values to the max length allowed */
  @Input() useCut?: false;

  /** Emitted on blur event */
  @Output() blurEvent = new EventEmitter<any>();

  get isCutVisible(): boolean {
    return (
      this.control != null &&
      this.control.value &&
      this.useCut &&
      this.hintMax != null &&
      this.control.value.length > this.hintMax
    );
  }

  cutText() {
    if (this.isCutVisible && this.control != null) {
      this.control.setValue(this.control.value.slice(0, this.hintMax));
      this.control.markAsDirty();
      this.control.updateValueAndValidity();
    }
  }

  protected override get fieldCssClasses(): string {
    let classes = super.fieldCssClasses;

    if (this.hint) {
      classes += ' use-hint';
    }
    if (this.hintMax) {
      classes += ' use-hint-max';
    }
    return classes;
  }
}
