# imports

```bash
artisan db:wipe && artisan migrate && artisan db:seed --class=AnswerSeeder && \
artisan import:equipos --database hojaschequeo --username carlos --password carlos1030 && \
artisan db:seed --class=UserSeeder && \
artisan import:perfils --database hojaschequeo --username carlos --password carlos1030 && \
artisan import:users --database hojaschequeo --username carlos --password carlos1030 &&  \
artisan import:tarjetons --database hojaschequeo --username carlos --password carlos1030 && \
artisan import:reportes --database hojaschequeo --username carlos --password carlos1030 && \
artisan import:hojas --database hojaschequeo --username carlos --password carlos1030 && \
```
