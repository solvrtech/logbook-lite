import { HttpClient } from '@angular/common/http';
import { AbstractControl, FormArray, FormGroup, ValidationErrors, ValidatorFn } from '@angular/forms';
import { environment } from 'src/environments/environment';

export class CustomValidators {
  constructor(protected http: HttpClient) {}
  /**
   * FormGroup validator - to validate password and `confirm password` control to have matching value
   * @param firstControlName name of the first control in FormGroup whose value to be matched
   * @param secondControlName name of the second control in FormGroup that should have matched values
   * @returns ValidatorFn
   */
  static fieldConfirm(firstControlName: string, secondControlName: string): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const password = control.get(firstControlName)?.value;
      const confirmPassword = control.get(secondControlName)?.value;

      if (password !== confirmPassword) {
        control.get(secondControlName)?.setErrors({ notmatch: true });
      }
      return null;
    };
  }

  /**
   * FormGroup validator - to validate multiple controls to have matching value
   * @param controlNames array of control names to be checked for matched values
   * @returns ValidatorFn
   */
  static fieldMatches(controlNames: string[]): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (controlNames && controlNames.length > 1) {
        const controls: AbstractControl[] = [];
        // get form controls first, based on `controlNames`
        controlNames.forEach(controlName => {
          const formControl = control.get(controlName);
          if (formControl != null) {
            controls.push(formControl);
          }
        });

        // for each form controls, check if there are unmatched value(s)
        if (controls.length > 1) {
          // reset errors first
          controls.forEach(control => control.setErrors(null));

          const initValue = controls[0].value;
          if (controls.find(control => control.value !== initValue) != null) {
            controls.forEach(control => control.setErrors({ notmatch: true }));
          }
        }
      }
      return null;
    };
  }

  /**
   * Make sure there is no whitespace character inside control's value
   */
  static whitespaceValidator(control: AbstractControl): ValidationErrors | null {
    return control && !control.disabled && control.value && /\s/g.test(control.value) ? { hasspace: true } : null;
  }

  /**
   * Make sure there is no whitespace character inside control's value
   */
  static valueIsNot(value: string | null): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      if (value != null && control && control.value != null && control.value.trim() === value.trim()) {
        return { valueisnot: true };
      }
      return null;
    };
  }

  /**
   * Validates control's value to match password regex as defined on env
   */
  static passwordFormat(control: AbstractControl): ValidationErrors | null {
    if (control && !control.disabled && control.value != null) {
      if (environment.userPasswordRegex.test(control.value)) {
        return null;
      } else {
        return { invalidpassword: true };
      }
    }
    return null;
  }

  /**
   * Validates control's value to match url regex as defined on env
   *
   * @param {AbstractControl} control
   * @return
   */
  static urlFormat(control: AbstractControl): ValidationErrors | null {
    if (control && !control.disabled && control.value != null) {
      if (environment.urlRegex.test(control.value)) {
        return null;
      } else {
        return { invalidurl: true };
      }
    }
    return null;
  }

  /**
   * Custom validator for FormGroup to make at least one field to be validator using the given `validator`
   */
  static atLeastOneValidator =
    (validator: ValidatorFn) =>
    (group: FormGroup): ValidationErrors | null => {
      const hasAtLeastOne =
        group && group.controls && Object.keys(group.controls).some(k => !validator(group.controls[k]));

      return hasAtLeastOne ? null : { atLeastOne: true };
    };

  /**
   * Custom Validator for FormArray has duplicate
   */
  static hasDuplicate(key_form: string, duplicated: any = []): ValidatorFn | any {
    return (control: AbstractControl): ValidationErrors | null => {
      if (control instanceof FormArray) {
        if (duplicated) {
          for (const i of duplicated) {
            control.at(i)?.get(key_form)?.setErrors(null);
          }
        }

        let dict: any = {};
        control.value.forEach((item: any, index: any) => {
          dict[item.key] = dict[item.key] || [];
          dict[item.key].push(index);
        });

        let duplicates: any = [];
        for (let key in dict) {
          if (dict[key].length > 1) duplicates = duplicates.concat(dict[key]);
        }
        duplicated = duplicates;

        for (const index of duplicates) {
          control.at(+index).get(key_form)?.setErrors({ duplicated: true });
        }

        return duplicates.length > 0 ? { errorRepeat: true } : null;
      }

      return null;
    };
  }
}
