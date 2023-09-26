import { Component, EventEmitter, Input, OnDestroy, Output } from '@angular/core';
import { FormControl, ValidationErrors } from '@angular/forms';
import { Observable, Subject } from 'rxjs';

/**
 * Base component class for generic form fields that use Material forms
 */
@Component({
  template: '',
})
export class BaseFormFieldComponent implements OnDestroy {
  /** Mandatory - FormControl for reactive form driven */
  @Input() control!: FormControl;

  /** ValidationErrors object for error messages on the component */
  @Input() errorTranslations: any = [];

  /** Label for form field */
  @Input() label = '';

  /** Hint text for form field */
  @Input() hint = '';

  /** CSS classes for the mat-form-field wrapper */
  @Input() fieldClasses = '';

  /** Optional Observable to be listened (for click event on component) */
  @Input() triggerClick$?: Observable<boolean>;

  /** Emitted when form value is changed */
  @Output() valueChanged = new EventEmitter<any>();

  @Output() save = new EventEmitter<any>();

  /** Subject that emits when the component has been destroyed. */
  protected onDestroy$ = new Subject<void>();

  ngOnDestroy() {
    this.onDestroy$.next();
    this.onDestroy$.complete();
  }

  /**
   * Returns string of error translation key of the first validation error that
   * might occured during validation process. We only need the first one, since
   * only one error message will be displayed at one time on <mat-error>.
   */
  getErrorString(validationErrors: ValidationErrors | null): string {
    if (this.errorTranslations && validationErrors) {
      // be careful, we can get a lowercase string instead from [errors]
      const firstErrorKey = Object.keys(validationErrors)[0];

      if (!firstErrorKey) {
        return '';
      }

      let errKey: any = null;
      const errTranslation = this.errorTranslations.find((err: any) =>
        Object.keys(err).find(key => {
          if (key.toLowerCase() === firstErrorKey.toLowerCase()) {
            errKey = key;
            return true;
          }
          return false;
        })
      );
      return errTranslation ? errTranslation[errKey] : 'common.form.invalid';
    }
    return '';
  }

  /**
   * Returns true if 'required' validation is found on validation rules
   */
  get isRequired(): boolean {
    if (this.control && this.control.validator) {
      const validators = this.control.validator(this.control);
      return Object.keys(validators != null ? validators : {}).find(key => key === 'required') != null;
    }
    return false;
  }

  protected get fieldCssClasses(): string {
    return this.fieldClasses
      ? this.fieldClasses
      : `${this.isRequired ? 'no-required-sign required' : 'no-required-sign'}`;
  }

  onReset(event: MouseEvent) {
    event.stopPropagation();
    if (this.control) {
      this.control.reset();
    }
  }

  onChange(event: any) {
    this.save.emit(event);
  }
}
