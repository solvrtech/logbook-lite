import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { Team } from '../interfaces/team.interface';
import { TeamService } from '../services/team.service';

export class TeamDataSource extends TableDataSource<Team> {
  constructor(private teamService: TeamService) {
    super();
  }

  get isPaginated(): boolean {
    return true;
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.startLoading();

    this.teamService.getTeams(option).subscribe({
      next: data => {
        this.stopLoading();

        if (data != null) {
          this.rowSubject$.next(data.items);
          this.countSubject$.next(data.totalItems);
        }
      },
      error: err => {
        console.log(err);
        this.stopLoading();
      },
    });
  }
}
