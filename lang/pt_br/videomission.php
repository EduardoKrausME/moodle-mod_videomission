<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brazilian Portuguese language strings.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmission'] = 'Adicionar missão';
$string['addoccurrence'] = 'Adicionar ocorrência';
$string['answers'] = 'Respostas';
$string['backtoactivity'] = 'Voltar para a atividade';
$string['completed'] = 'Concluída';
$string['completiondetail:missions'] = 'Concluir todas as missões obrigatórias';
$string['completiondetail:watch'] = 'Assistir pelo menos {$a}% do vídeo';
$string['completionmissions'] = 'Exigir todas as missões obrigatórias';
$string['completionmissions_help'] = 'A atividade só é concluída quando todas as missões obrigatórias forem concluídas.';
$string['completionwatch'] = 'Exigir percentual assistido';
$string['completionwatch_help'] = 'Percentual mínimo único do vídeo para conclusão. Use 0 para desativar.';
$string['confirmdeletemission'] = 'Excluir a missão "{$a}"?';
$string['confirmtask'] = 'Confirmo que concluí esta tarefa';
$string['csvfilename'] = 'relatorio-video-mission';
$string['deletemission'] = 'Excluir missão';
$string['editmission'] = 'Editar missão';
$string['exportcsv'] = 'Exportar CSV';
$string['grade'] = 'Nota máxima';
$string['invalidmission'] = 'Missão inválida.';
$string['invalidresponse'] = 'A resposta da missão está incompleta.';
$string['invalidvideo'] = 'Não foi possível carregar o vídeo.';
$string['lastaccess'] = 'Última atualização';
$string['locked'] = 'Bloqueada';
$string['managemissions'] = 'Gerenciar missões';
$string['mandatoryprogress'] = '{$a->completed} de {$a->total} missões obrigatórias concluídas';
$string['missiondescription'] = 'Instruções';
$string['missionlist'] = 'Lista de missões';
$string['missionlocked'] = 'Conclua a missão anterior para liberar esta missão.';
$string['missionlockederror'] = 'Esta missão ainda está bloqueada.';
$string['missionname'] = 'Nome da missão';
$string['missionoptional'] = 'Opcional';
$string['missionorder'] = 'Ordem das missões';
$string['missionorder_help'] = 'No modo sequencial, a próxima missão é liberada quando a anterior for concluída.';
$string['missionorderany'] = 'Qualquer ordem';
$string['missionordersequential'] = 'Sequencial';
$string['missionpoints'] = '{$a} pontos';
$string['missionprogress'] = '{$a->completed} de {$a->total} missões concluídas';
$string['missionrequired'] = 'Obrigatória';
$string['missionscompleted'] = 'Missões concluídas';
$string['missionspending'] = 'Missões pendentes';
$string['missiontype'] = 'Tipo de missão';
$string['missiontypeconfirm'] = 'Confirmar uma tarefa';
$string['missiontypeinterval'] = 'Selecionar um intervalo';
$string['missiontypemoment'] = 'Selecionar um momento';
$string['missiontypeobservation'] = 'Escrever uma observação';
$string['missiontypeoccurrences'] = 'Encontrar ocorrências';
$string['missiontypetext'] = 'Responder uma pergunta';
$string['modulename'] = 'Video Mission';
$string['modulename_help'] = 'Transforma um vídeo em uma atividade por missões com objetivos, evidências em momentos do vídeo, respostas e acompanhamento de progresso.';
$string['modulenameplural'] = 'Video Missions';
$string['movedown'] = 'Mover para baixo';
$string['moveup'] = 'Mover para cima';
$string['noattempts'] = 'Ainda não existem tentativas de estudantes.';
$string['nomissions'] = 'Ainda não existem missões.';
$string['notgraded'] = 'Sem nota';
$string['occurrences'] = 'Ocorrências';
$string['optional'] = 'Opcional';
$string['overallprogress'] = 'Progresso da atividade';
$string['pending'] = 'Pendente';
$string['pluginadministration'] = 'Administração do Video Mission';
$string['pluginname'] = 'Video Mission';
$string['points'] = 'Pontos';
$string['privacy:metadata:videomission_answers'] = 'Armazena as respostas dos estudantes às missões.';
$string['privacy:metadata:videomission_answers:completed'] = 'Indica se a missão foi concluída.';
$string['privacy:metadata:videomission_answers:endtime'] = 'Fim do intervalo selecionado.';
$string['privacy:metadata:videomission_answers:occurrences'] = 'Momentos selecionados como ocorrências.';
$string['privacy:metadata:videomission_answers:response'] = 'Resposta textual da missão.';
$string['privacy:metadata:videomission_answers:score'] = 'Pontos obtidos na missão.';
$string['privacy:metadata:videomission_answers:starttime'] = 'Momento selecionado ou início do intervalo.';
$string['privacy:metadata:videomission_answers:timemodified'] = 'Data da última alteração da resposta.';
$string['privacy:metadata:videomission_answers:userid'] = 'Usuário que enviou a resposta.';
$string['privacy:metadata:videomission_progress'] = 'Armazena o progresso de visualização do vídeo.';
$string['privacy:metadata:videomission_progress:duration'] = 'Duração conhecida do vídeo.';
$string['privacy:metadata:videomission_progress:lastposition'] = 'Última posição de reprodução.';
$string['privacy:metadata:videomission_progress:percentage'] = 'Percentual único do vídeo assistido.';
$string['privacy:metadata:videomission_progress:playtime'] = 'Tempo total de reprodução, incluindo repetições.';
$string['privacy:metadata:videomission_progress:timemodified'] = 'Data da última atualização do progresso.';
$string['privacy:metadata:videomission_progress:userid'] = 'Usuário cujo progresso é armazenado.';
$string['privacy:metadata:videomission_progress:watchedseconds'] = 'Segundos únicos assistidos.';
$string['privacy:metadata:videomission_progress:watchedsegments'] = 'Trechos do vídeo assistidos pelo usuário.';
$string['progresssaved'] = 'Progresso do vídeo salvo.';
$string['removeoccurrence'] = 'Remover';
$string['report'] = 'Relatório';
$string['required'] = 'Missão obrigatória';
$string['requiredcount'] = 'Quantidade de ocorrências exigida';
$string['requiredcount_help'] = 'Usado apenas em missões que pedem ao estudante para encontrar ocorrências repetidas no vídeo.';
$string['requiredwatch'] = 'Percentual recomendado de visualização';
$string['requiredwatch_help'] = 'Percentual exibido como objetivo de visualização. A conclusão pode usar um limite independente.';
$string['response'] = 'Resposta';
$string['restrictseek'] = 'Restringir avanço para trechos não assistidos';
$string['restrictseek_help'] = 'Quando ativado, o estudante não pode avançar muito além do trecho já assistido.';
$string['resumeplayback'] = 'Retomar da última posição';
$string['resumeplayback_help'] = 'Retoma o vídeo da última posição salva.';
$string['saved'] = 'Salvo';
$string['savemission'] = 'Salvar missão';
$string['score'] = 'Pontuação';
$string['selectedinterval'] = 'Intervalo selecionado';
$string['selectedmoment'] = 'Momento selecionado';
$string['setend'] = 'Definir fim';
$string['setstart'] = 'Definir início';
$string['source'] = 'Fonte do vídeo';
$string['sourceupload'] = 'Upload';
$string['sourceurl'] = 'URL direta';
$string['sourcevimeo'] = 'Vimeo';
$string['sourceyoutube'] = 'YouTube';
$string['student'] = 'Estudante';
$string['timeminutesseconds'] = '{$a->minutes}:{$a->seconds}';
$string['totalscore'] = 'Total de pontos';
$string['tracking'] = 'Acompanhamento e navegação';
$string['unknownsource'] = 'A fonte de vídeo configurada não é válida.';
$string['usecurrenttime'] = 'Usar tempo atual';
$string['videofile'] = 'Arquivo de vídeo';
$string['videofile_help'] = 'Envie um arquivo de vídeo para o Moodle.';
$string['videomission:addinstance'] = 'Adicionar uma nova atividade Video Mission';
$string['videomission:manage'] = 'Gerenciar missões do Video Mission';
$string['videomission:submit'] = 'Enviar respostas do Video Mission';
$string['videomission:view'] = 'Visualizar atividades Video Mission';
$string['videomission:viewreports'] = 'Visualizar relatórios do Video Mission';
$string['videoprogress'] = 'Vídeo assistido: {$a}%';
$string['videourl'] = 'URL do vídeo';
$string['videourl_help'] = 'Informe uma URL direta de vídeo, YouTube ou Vimeo conforme a fonte escolhida.';
$string['watched'] = 'Assistido';
$string['watchgoal'] = 'Objetivo de visualização: {$a}%';
