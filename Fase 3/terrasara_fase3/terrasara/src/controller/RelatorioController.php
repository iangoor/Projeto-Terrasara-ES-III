<?php
class RelatorioController extends Controller
{
    /** UC07/UC08/UC09 — relatório financeiro por período. */
    public function financeiro()
    {
        $inicio = $this->dataValida($this->get('inicio'), date('Y-m-01'));
        $fim = $this->dataValida($this->get('fim'), date('Y-m-d'));
        $erro = null;
        if ($inicio > $fim) {
            $erro = 'A data inicial não pode ser maior que a data final.';
            $inicio = $fim;
        }

        $dao = new RelatorioDAO();
        $this->render('relatorio/financeiro', array(
            'titulo' => 'Relatório financeiro', 'inicio' => $inicio, 'fim' => $fim, 'erro' => $erro,
            'resumo' => $dao->resumo($inicio, $fim),
            'maisVendidos' => $dao->maisVendidos($inicio, $fim, 10),
            'pendentes' => $dao->pagamentosPendentes(15),
        ));
    }

    private function dataValida($valor, $padrao)
    {
        $d = DateTime::createFromFormat('Y-m-d', (string) $valor);
        return ($d && $d->format('Y-m-d') === $valor) ? $valor : $padrao;
    }
}
