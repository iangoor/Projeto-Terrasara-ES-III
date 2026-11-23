# Testes automatizados — TerraSara / Shizen Orgânicos

Suíte sem dependências (não precisa de Composer nem PHPUnit). Usa **SQLite em memória**:
o MySQL real nunca é tocado.

## Pré-requisitos
- PHP 5.6+ (recomendado 7.4+) com as extensões `pdo` e `pdo_sqlite` ligadas no `php.ini`.

## Como rodar
```
php tests/run.php              # todos os testes
php tests/run.php Venda        # só as classes cujo nome contém "Venda"
```
Código de saída `0` = tudo passou, `1` = houve falha (serve para CI).

## Estrutura
- `bootstrap.php` – carrega o autoload do sistema.
- `TesteBase.php` – mini framework (asserções, banco SQLite novo por teste, fábricas de dados).
- `Casos/*Test.php` – os casos (prefixo `testCTnn_` = caso do Plano de Testes).
