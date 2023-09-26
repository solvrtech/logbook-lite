import { Component, Inject } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

export interface ConfirmDialogData {
  title: string;
  message: string;
  item: string;
  inputName: boolean;
  link: string;
  buttonOk: boolean;
}

@Component({
  selector: 'app-confirm-dialog',
  templateUrl: './confirm-dialog.component.html',
  styleUrls: ['./confirm-dialog.component.scss'],
})
export class ConfirmDialogComponent {
  title: string;
  message: string;
  item: string;
  inputName: boolean = false;
  link: string;
  buttonOk: boolean;

  form!: FormGroup;

  constructor(
    private fb: FormBuilder,
    private dialogRef: MatDialogRef<ConfirmDialogComponent>,
    @Inject(MAT_DIALOG_DATA) data: ConfirmDialogData
  ) {
    this.title = data.title;
    this.message = data.message;
    this.item = data.item;
    this.inputName = data.inputName;
    this.link = data.link;
    this.buttonOk = data.buttonOk;

    this.form = this.fb.group({
      name: new FormControl('', [Validators.required]),
    });
  }

  onConfirm() {
    this.inputName ? this.dialogRef.close(this.form.value) : this.dialogRef.close(true);
  }

  onDismiss() {
    this.dialogRef.close(false);
  }
}
