import { Component, EventEmitter, Input, Output } from '@angular/core';
import { BaseFormFieldComponent } from './form-field.base';

/**
 * Generic wrapper component for <textarea>'s mat-input using reactive form driven technique.
 */
@Component({
  selector: 'app-form-textarea',
  templateUrl: './form-textarea.component.html',
})
export class FormTextAreaComponent extends BaseFormFieldComponent {
  @Input() minRows = 1;
  @Input() maxRows: number | null = null;

  @Input() hintMax: number | null = null;

  /** Emitted on blur event */
  @Output() blurEvent = new EventEmitter<any>();

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
