import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { takeUntil } from 'rxjs/operators';
import { AuthService } from 'src/app/login/services/auth/auth.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { CustomValidators } from 'src/app/shared/helpers/validators.helper';
import { FormDropdown, FormPageProps } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AppRole } from 'src/app/starter/data/permissions.data';
import { environment } from 'src/environments/environment';
import { RequestUser, User } from '../../../interfaces/user.interface';
import { RoleService } from '../../../services/role.service';
import { UserService } from '../../../services/user.service';

@Component({
  selector: 'app-user-editor',
  templateUrl: './user-editor.component.html',
  styleUrls: ['./user-editor.component.scss'],
})
export class UserEditorComponent extends BaseSecurePageComponent implements OnInit {
  // if set, this will override default page's title
  @Input() title!: string;

  // it will stay on page after successful `save()` when this is false
  @Input() redirectAfterSave: boolean = true;

  // if set, this will override the default `pagePermissions`
  @Input() permissions!: string[];

  // 'SELF' for editing his/her own user, 'OTHER' is when doing create or edit other user's data
  @Input() mode: 'SELF' | 'OTHER' = 'OTHER';

  // if set, this will be used to fetch User data model for this page (and `edit` will be true)
  @Input() userId!: number | undefined;

  // emit saved User data after successful `save()`
  @Output() saved = new EventEmitter<User>();

  // the page's state
  override pageState: FormPageProps = {
    state: 'loading',
    returnUrl: '/administration/users',
  };

  // the page editor's mode (for creating or editing)
  edit = false;

  // FormGroup for the page's <form>
  formGroup!: FormGroup;

  // holds dropdown data for `roles` form control
  roles: FormDropdown[] = [];
  role: any = [];

  // data model for this page
  user!: User;

  // link for this page
  link!: string;

  // Breadcrumb for this page
  breadCrumbs = [
    {
      url: '/administration/users',
      label: 'title.users',
    },
    {
      url: '',
      label: this.activateRoute.snapshot.params['id'] ? 'common.edit' : 'common.create',
    },
  ];

  // min length for `password` form control
  minPasswordLength = environment.userPasswordMinLength;

  // max length for `password` form control
  maxPasswordLength = environment.userPasswordMaxLength;

  // custom translation strings for specific form control errors
  errorTranslations = {
    password: [{ invalidpassword: 'errors.field.invalid_password' }],
    confirmPassword: [
      {
        notmatch: 'errors.field.password_confirm_not_match',
      },
    ],
  };

  constructor(
    roleService: RoleService,
    private userService: UserService,
    private activateRoute: ActivatedRoute,
    private fb: FormBuilder,
    private router: Router,
    private alertService: AlertsService,
    public authService: AuthService
  ) {
    super(roleService);
    this.initFormGroup();

    // get query params that should be sent from previous page (`UsersComponent`)
    this.initQueryParams(this.router);
  }

  /**
   * Get permission to create and edit user page
   *
   * @return {string}
   */
  get pagePermissions(): string[] {
    return this.permissions ? this.permissions : [AppRole.ROLE_ADMIN];
  }

  onInit(): void {
    // Notes:
    // MyProfileComponent doesn't work correctly if mode selection is executed on parallel with `this.fetchRoles()`.
    // Component's mode selection (of CREATE or EDIT) works when it is executed afterward `this.fetchRoles()`.
    this.fetchRoles(() => {
      // set mode:
      if (this.userId != null) {
        // EDIT mode, as triggered from host component (ex: MyProfileComponent) :
        this.initEditMode(this.userId);
      } else {
        this.activateRoute.params.pipe(takeUntil(this.onDestroy$)).subscribe({
          next: params => {
            setTimeout(() => {
              if (params['id']) {
                // EDIT mode
                this.initEditMode(params['id']);
              } else {
                // CREATE mode
                this.initCreateMode();
              }
            });
          },
          error: err => {
            console.log(err);
          },
        });
      }
    });
  }

  /**
   * Setup form controls and initial validators
   *
   * @privateW
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      name: new FormControl('', [Validators.required]),
      email: new FormControl('', [Validators.required, Validators.email]),
      role: new FormControl('', [Validators.required]),
      password: new FormControl('', []),
      confirmPassword: new FormControl('', []),
      changePassword: new FormControl('', []),
    });
  }

  /**
   * Build dropdown data for `roles` field
   *
   * @private
   */
  private fetchRoles(callback?: any) {
    this.roleService
      .getRolesDropdown()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe({
        next: roles => {
          this.roles = roles;
          if (typeof callback === 'function') {
            callback();
          }
        },
        error: err => {
          console.log(err);
        },
      });
  }

  /**
   * Event handler for `change password` toggle
   * @param toChange enabled or disabled state
   */
  onChangePasswordChanged(toChange: boolean) {
    toChange ? this.enablePasswordValidators() : this.disablePasswordValidators();
  }

  /**
   * Enables needed validators for `password`, `confirmPassword`, and FormGroup
   */
  private enablePasswordValidators() {
    const passwordControl = this.formGroup.controls['password'];
    const confirmPasswordControl = this.formGroup.controls['confirmPassword'];
    passwordControl.setValidators([
      Validators.minLength(this.minPasswordLength),
      Validators.maxLength(this.maxPasswordLength),
      CustomValidators.passwordFormat,
    ]);
    confirmPasswordControl.setValidators([
      Validators.minLength(this.minPasswordLength),
      Validators.maxLength(this.maxPasswordLength),
    ]);
    this.formGroup.setValidators(CustomValidators.fieldConfirm('password', 'confirmPassword'));
    this.formGroup.updateValueAndValidity();
  }

  /**
   * Disables validators for `password`, `confirmPassword`, and FormGroup
   *
   * @private
   */
  private disablePasswordValidators() {
    const passwordControl = this.formGroup.controls['password'];
    const confirmPasswordControl = this.formGroup.controls['confirmPassword'];
    passwordControl.setValue(null);
    passwordControl.setValidators(null);
    confirmPasswordControl.setValue(null);
    confirmPasswordControl.setValidators(null);
    this.formGroup.setValidators(null);
    this.formGroup.updateValueAndValidity();
  }

  /**
   * Helper to init form controls in CREATE mode
   *
   * @private
   */
  private initCreateMode() {
    // `password` and `confirm password` are not visible when creating new user
    this.enablePasswordValidators();

    this.formGroup.updateValueAndValidity();
    // mandatory or the page will be blank:
    this.pageState.state = 'loaded';
  }

  /**
   * Helper to init form controls in EDIT mode
   *
   * @param {number} id : user id
   * @private
   */
  private initEditMode(id: number) {
    this.edit = true;

    // `roles` and `enabled` fields is editable on specific permission only
    if (!this.isCurrentUserHas([AppRole.ROLE_ADMIN])) {
      this.formGroup.controls['role'].disable();
    }
    this.fetchModel(id);
  }

  /**
   * Fetch user data with the given id
   * @param {number} id : user id
   * @private
   */
  private fetchModel(id: number) {
    // getting current user vs other user data requires different permission and endpoint, so we do this:
    const observable =
      id === this.authService.currentUser?.id ? this.userService.getCurrentUser() : this.userService.getUserId(id);

    observable.subscribe({
      next: res => {
        if (res && res.success) {
          this.user = res.data;

          this.formGroup.controls['name'].setValue(this.user.name);
          this.formGroup.controls['email'].setValue(this.user.email);
          this.formGroup.controls['role'].setValue(this.user.role);
          this.formGroup.controls['password'].setValue('');

          this.pageState.state = 'loaded';
        } else {
          this.pageState.state = 'error';
          this.pageState.message = _('error.msg.error_while_loading_user_data');
        }
      },
      error: err => {
        console.log(err);
      },
    });
  }

  /**
   * Build request payload for saving user data
   * @returns RequestUser
   * @private
   */
  private buildUserRequest(): RequestUser {
    return {
      name: this.formGroup.controls['name'].value,
      email: this.formGroup.controls['email'].value,
      role: this.formGroup.controls['role'].value,
      password: this.formGroup.controls['password'].value,
    };
  }

  /**
   * Saves or updates user data
   */
  save() {
    if (this.formGroup.valid) {
      let observable =
        this.edit && this.mode === 'OTHER'
          ? this.userService.updateUser(this.user.id, this.buildUserRequest())
          : this.mode === 'SELF'
          ? this.userService.updateProfile(this.buildUserRequest())
          : this.userService.createUser(this.buildUserRequest());

      observable.subscribe({
        next: res => {
          if (res && res.success) {
            if (this.redirectAfterSave) {
              this.router.navigate(['/administration/users'], {
                queryParams: this.queryParams,
              });
            }
            this.alertService.setSuccess(_('user.msg.user_saved'));
            this.saved.emit(res.data);
          } else {
            if (res.data) {
              if (res.data.duplicatedEmail) this.alertService.setError(_('user.msg.user_duplicated'));
            } else {
              this.alertService.setError(_('user.msg.user_save.error'));
            }
          }
        },
        error: err => {
          this.alertService.setError(_('user.msg.user_save.error'));
        },
      });
    }
  }
}
