import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { Message } from 'src/app/messages/interfaces/message.interface';
export interface MessageIcon {
  id: number;
  icon: any;
  createdAt: Date;
  message: Message;
}
@Component({
  selector: 'app-dialog-message',
  templateUrl: './dialog-message.component.html',
  styleUrls: ['./dialog-message.component.scss'],
})
export class DialogMessageComponent {
  id: number;
  iconMessage: any;
  createdAt!: Date;
  message!: Message;

  constructor(private dialogRef: MatDialogRef<DialogMessageComponent>, @Inject(MAT_DIALOG_DATA) data: MessageIcon) {
    this.id = data.id;
    this.iconMessage = data.icon;
    this.createdAt = data.createdAt;
    this.message = data.message;
  }

  onDismiss() {
    this.dialogRef.close(false);
  }
}
