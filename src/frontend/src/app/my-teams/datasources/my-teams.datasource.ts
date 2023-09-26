import { Team } from 'src/app/administration/interfaces/team.interface';
import { TeamService } from 'src/app/administration/services/team.service';
import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';

export class MyTeamsDataSource extends TableDataSource<Team> {
  constructor(private teamService: TeamService) {
    super();
  }
  get isPaginated(): boolean {
    return true;
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.teamService.getTeams(option).subscribe({
      next: data => {
        if (data != null) {
          this.rowSubject$.next(data.items);
          this.countSubject$.next(data.totalItems);
        }
      },
      error: err => console.log(err),
    });
  }
}
