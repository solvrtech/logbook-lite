import { Component, Input, OnInit } from '@angular/core';
import { takeUntil } from 'rxjs/operators';
import { BaseFormFieldComponent } from './form-field.base';

/**
 * Generic wrapper component for timepicker using reactive form driven
 * technique. It can be used through ValidatedFieldComponent or as a stand
 * alone component.
 */
@Component({
  selector: 'app-form-timepicker',
  templateUrl: './form-timepicker.component.html',
})
export class FormTimepickerComponent extends BaseFormFieldComponent implements OnInit {
  /** 12h or 24h view for hour selection clock. 24 hours format by default. */
  @Input() format: 12 | 24 = 24;

  @Input() disabled = false;

  /** max input length allowed, if null then it will not be used/visible */
  @Input() hintMax: number | null = null;

  ngOnInit() {
    this.control.valueChanges.pipe(takeUntil(this.onDestroy$)).subscribe(value => this.valueChanged.emit(value));
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
