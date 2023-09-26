import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { Location } from '@angular/common';
import { AfterViewChecked, ChangeDetectorRef, Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatChipInputEvent } from '@angular/material/chips';
import { Router } from '@angular/router';
import { Observable, Subscription } from 'rxjs';
import { debounceTime, delay, filter, map, takeUntil, tap } from 'rxjs/operators';
import { dateTo } from '../../helpers/date.helper';
import { hexFromBase64, hexToBase64 } from '../../helpers/helpers.helper';
import { CustomValidators } from '../../helpers/validators.helper';
import { SearchAttemptField, SearchAttemptFields, SearchFields } from '../../interfaces/search.interface';
import { AlertsService } from '../../services/alerts/alerts.service';
import { BaseComponent } from '../bases/base.component';

@Component({
  selector: 'app-search',
  templateUrl: './search.component.html',
  styleUrls: ['./search.component.scss'],
})
export class SearchComponent extends BaseComponent implements OnInit, AfterViewChecked {
  @Input() searchDisabled!: boolean;

  @Input() fields$!: Observable<SearchFields>;

  @Input() fieldColumns!: 1 | 2 | 3 | 4 | 5 | 6;

  @Input() id = 'search';

  @Input() panelClasses = 'search-panel mb-3';

  @Input() submitSearchOnInit = true;

  @Input() clearDefaultValueOnReset = false;

  @Input() triggerSearch$?: Observable<boolean> | null;

  @Input() triggerReset$?: Observable<boolean> | null;

  @Input() allowEmptySearch = true;

  @Input() rangeDate: number | any;

  @Output() searchClicked = new EventEmitter<SearchAttemptFields>();

  @Output() resetClicked = new EventEmitter<boolean>();

  @Output() search = new EventEmitter<SearchAttemptFields | null>();

  formGroup: any = FormGroup;
  searchFields!: SearchFields;
  searchFieldsSubscription!: Subscription;
  fieldColumnClasses = 'col-12';
  lastFieldColumnClass = '';
  searchOpened = true;
  initialSearch = 0;
  searching = false;
  chips: any = [];
  length!: number;
  maxDate = new Date();
  days: number | any;
  readonly separatorKeysCodes = [ENTER, COMMA] as const;

  constructor(
    private fb: FormBuilder,
    private router: Router,
    private location: Location,
    private cdRef: ChangeDetectorRef,
    private alerts: AlertsService
  ) {
    super();
  }

  ngOnInit() {
    this.fields$.subscribe(res => {
      this.length = res.length;
    });
    this.searchDrop();

    this.subscribeSearchField();
    this.setColumnClasses();
    if (this.triggerReset$ != null) {
      this.triggerReset$.pipe(takeUntil(this.onDestroy$)).subscribe(resetNow => {
        if (resetNow === true) {
          this.onClear();
        }
      });
    }
    this.search.emit(null);
  }

  ngAfterViewChecked() {
    /**
     * Check if formgroup has key from and to and both are instance of Date
     */
    if (this.formGroup.value.startDateTime instanceof Date && this.formGroup.value.endDateTime instanceof Date) {
      /**
       * If to value is less than from, change to value accordingly
       */
      if (this.formGroup.value.startDateTime > this.formGroup.value.endDateTime) {
        this.formGroup.patchValue({
          endDateTime: dateTo(this.formGroup.value.startDateTime),
        });
      }
    }

    const startDateTime = this.formGroup.controls['startDateTime'];
    const endDateTime = this.formGroup.controls['endDateTime'];

    // Set validator search field key `start and end` date time
    if (startDateTime?.value != null && endDateTime?.value == null) {
      endDateTime.setErrors({ required: true });
    } else if (startDateTime?.value == null && endDateTime?.value != null) {
      startDateTime.setErrors({ required: true });
    } else if (startDateTime?.value == null && endDateTime?.value == null) {
      startDateTime?.setErrors(null);
      endDateTime?.setErrors(null);
    }

    this.cdRef.detectChanges();
  }

  /**
   * Set column css form field search
   *
   * @returns
   */
  setColumnClasses() {
    switch (this.fieldColumns) {
      case 1:
        this.fieldColumnClasses = 'col-12';
        break;
      case 2:
        this.fieldColumnClasses = 'col-12 col-xl-6';
        break;
      case 3:
        this.fieldColumnClasses = 'col-12 col-xl-4 col-lg-6';
        break;
      case 4:
        this.fieldColumnClasses = 'col-12 col-xl-3 col-lg-6';
        break;
      case 5:
        this.fieldColumnClasses = 'col-12 col-lg-2 col-md-6';
        this.lastFieldColumnClass = 'col-12 col-lg-4 col-md-6';
        break;
      case 6:
        this.fieldColumnClasses = 'col-12 col-md-3';
        this.lastFieldColumnClass = 'col-12 col-md-6';
        break;
      default:
        this.fieldColumnClasses = 'col-12';
        break;
    }
  }

  /**
   * Helper search live dropdown
   *
   * @returns
   */
  searchDrop() {
    this.fields$.pipe(takeUntil(this.onDestroy$)).subscribe({
      next: fields => {
        this.searchFields = fields;
        this.searchFields.forEach(field => {
          if (field.type == 'DROPSEARCH') {
            field.options?.subscribe(filteredOptions => {
              field.searchForm.valueChanges
                .pipe(
                  filter(search => !!search),
                  tap(() => (this.searching = true)),
                  takeUntil(this.onDestroy$),
                  debounceTime(200),
                  map((search: any) => {
                    if (!field.options) {
                      return [];
                    }

                    // simulate server fetching and filtering data
                    return filteredOptions.filter(
                      option => option.label.toLowerCase().indexOf(search.toLowerCase()) > -1
                    );
                  }),
                  delay(500)
                )
                .subscribe(
                  (filterOptions: any) => {
                    this.searching = false;
                    field.filteredOptions?.next(filterOptions);
                  },
                  () => {
                    // no errors in our simulated example
                    this.searching = false;
                    // handle error...
                  }
                );
            });
          }
        });
      },
    });
  }

  subscribeSearchField() {
    this.searchFieldsSubscription = this.fields$.pipe(takeUntil(this.onDestroy$)).subscribe({
      next: fields => {
        this.searchFields = fields;
        this.formGroup = this.fb.group(this.buildFormGroup(fields));
        if (this.allowEmptySearch === false) {
          this.formGroup.setValidators(CustomValidators.atLeastOneValidator(Validators.required));
          this.formGroup.updateValueAndValidity();
        }

        // if default value is set, then run submit first
        if (this.submitSearchOnInit && this.initialSearch > 0 && this.initialSearch <= this.fieldColumns) {
          this.onSubmit();
        }
      },
      complete: () => {},
    });

    if (this.triggerSearch$ != null) {
      this.triggerSearch$.pipe(takeUntil(this.onDestroy$)).subscribe(searchNow => {
        if (searchNow === true) {
          this.onSubmit();
        }
      });
    }
  }

  /**
   * Build search form groups
   */
  buildFormGroup(fields: SearchFields): { [key: string]: FormControl } {
    const group: any = {};
    fields.forEach(field => {
      /**
       * if default value is set, add more initial search
       */
      if (this.initialSearch >= 0 && this.initialSearch <= this.fieldColumns) {
        if (typeof field.default !== 'undefined' && field.default !== null) {
          this.initialSearch += 1;
        }
      }
      group[field.key] = new FormControl(
        // if field.default is not null
        typeof field.default !== 'undefined' && field.default !== null && field.toHex === true
          ? hexToBase64(field.default)
          : field.default,
        field.validators
      );
    });
    return group;
  }

  /**
   * Helper function to build SearchAttemptFields, that will be called on form submit.
   */
  buildSearchAttempt(): SearchAttemptFields {
    const resultFields: SearchAttemptField[] = [];
    this.searchFields.forEach(field => {
      const formControl = this.formGroup.controls[field.key];
      if (formControl.status === 'VALID') {
        let value = formControl.value;
        if (typeof field.getFormattedValue === 'function') {
          value = field.getFormattedValue(formControl.value);
        } else if (typeof formControl.value !== 'undefined' && formControl.value !== null && field.toHex === true) {
          value = hexFromBase64(formControl.value);
        }

        if (typeof formControl.value === 'undefined' || formControl.value === null) {
          value = '';
        }

        resultFields.push({
          field,
          value,
        });
      }
    });

    return {
      // build search filter string in query parameter format
      query: resultFields.length
        ? '?' +
          resultFields
            .map(
              attemptField => `${encodeURIComponent(attemptField.field.key)}=${encodeURIComponent(attemptField.value)}`
            )
            .join('&')
        : '',
      // build search filter string using semicolon-separated
      pattern: resultFields
        .map(attemptField => `${attemptField.field.key}:${encodeURIComponent(attemptField.value)}`)
        .join(';'),
      // build DataSearchParams
      params: {
        raw: Object.assign(
          {},
          ...resultFields.map(attemptField => ({
            [attemptField.field.key]: this.formGroup.controls[attemptField.field.key].value,
          }))
        ),
        formatted: Object.assign(
          {},
          ...resultFields.map(attemptField => ({
            [attemptField.field.key]: {
              label: attemptField.field.label,
              value: attemptField.value,
            },
          }))
        ),
      },
      // the SearchAttemptField[] that are being used as search params
      fields: resultFields,
    } as SearchAttemptFields;
  }

  onSubmit() {
    const searchAttempt = this.buildSearchAttempt();
    this.location.go(this.router.url.split('?')[0], searchAttempt.query);
    this.searchClicked.emit(searchAttempt);
    this.search.emit(searchAttempt);
  }

  onReset(key: string, filteredOptions: any) {
    this.formGroup.controls[key].setValue(null);
    if (filteredOptions) {
      filteredOptions.next([]);
    }
  }

  onClear() {
    this.chips = [];
    this.searchFields.forEach(field => {
      field.filteredOptions?.next([]);
    });

    this.formGroup.reset();
    this.initialSearch = 0;

    // on clear search field, re-subscribe search field and rebuild formgroup to show default value
    if (!this.clearDefaultValueOnReset) {
      this.searchFieldsSubscription.unsubscribe();
      this.subscribeSearchField();

      if (this.initialSearch == 0) {
        this.onSubmit();
      }
    }
  }

  get showResetButton(): boolean {
    return this.formGroup != null && this.isAnySearchValue;
  }

  get isAnySearchValue(): boolean {
    return this.formGroup ? !Object.values(this.formGroup.value).every(x => x === null || x === '') : false;
  }

  /**
   * Delete chip value if `index >= 0`
   *
   * @param {string} chips
   * @param {string} key
   */
  remove(chips: string, key: string) {
    const index = this.chips.indexOf(chips);

    if (index >= 0) {
      this.chips.splice(index, 1);
      this.formGroup.controls[key].setValue(this.chips);
    }

    if (index == 0) {
      this.formGroup.controls[key].setValue(null);
    }
  }

  /**
   * Add value for chips form field
   *
   * @param {MatChipInputEvent} event
   * @param {string} key
   */
  add(event: MatChipInputEvent, key: string): void {
    const value = (event.value || '').trim();
    const data = this.chips.filter((item: any) => item == value.toLowerCase());

    // Add our keyword
    if (value && data.length <= 0) {
      this.chips.push(value.toLowerCase());
      this.formGroup.controls[key].setValue(this.chips);
    }

    // clear the input value
    event.chipInput!.clear();
  }

  dateChange() {
    let start: any = this.formGroup.value.startDateTime ? new Date(this.formGroup.value.startDateTime) : null;
    let end: any = this.formGroup.value.endDateTime ? new Date(this.formGroup.value.endDateTime) : null;
    if (start && end) {
      end.setDate(end.getDate());
      let differenceInTime = end.getTime() - start.getTime();
      // To calculate the of days between two dates
      this.days = Math.floor(differenceInTime / (1000 * 3600 * 24));

      if (this.days > this.rangeDate) {
        this.alerts.setError('common.maxDate_errors');
      }
    }
  }
}
