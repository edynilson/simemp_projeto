<?php

include('../conf/check_pastas.php');

$query_dados_logo = $connection->prepare("SELECT b.path_imagem_print, b.print_height, b.print_width FROM banco b INNER JOIN conta c ON b.id=c.id_banco INNER JOIN empresa emp ON c.id_empresa=emp.id_empresa WHERE emp.ativo='1' AND emp.id_empresa=:id");
$query_dados_logo->execute(array(':id' => $_SESSION['id_empresa']));
$linha_dados_logo = $query_dados_logo->fetch(PDO::FETCH_ASSOC);

$datetime = new DateTime();

?>

<page backtop="10mm" backbottom="10mm">
    <page_footer>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left; width: 20%">Simemp <?php echo $datetime->format('Y'); ?></td>
                <td style="text-align: center; width: 60%">Documento válido apenas no Simemp &copy; &reg;</td>
                <td style="text-align: right; width: 20%">Pág. [[page_cu]]/[[page_nb]]</td>
            </tr>
        </table>
    </page_footer>
    
    <table cellspacing="0" style="width: 100%; font-size: 80%;">
        <tr>
            <td style="height: 20px;"></td>
        </tr>
        <tr>
            <td style="float: left; width: 50%; vertical-align: middle; height: <?php echo $linha_dados_logo['print_height'] ?>;"><img src="<?php echo '../' . $linha_dados_logo['path_imagem_print']; ?>" style="height: <?php echo $linha_dados_logo['print_height'] ?>; width: <?php echo $linha_dados_logo['print_width'] ?>;"></td>
            <td style="float: right; width: 50%; text-align: right; font-weight: bold;"> CONDIÇÕES GERAIS DE POUPANÇAS E <br> DEPÓSITOS A PRAZO <br> Caixa Agrícola</td>
        </tr>
        
        <tr>
            <td style="height: 80px;"></td>
        </tr>
    </table>
    
    <table cellspacing="0" style="width: 100%; font-size: 12pt; line-height: 1.5;">
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">A. DISPOSIÇÕES GERAIS</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Objecto</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">1.1.  Este  documento  contém  as  Condições  Gerais  de constituição  de  Produtos  de  Poupança  e/ou  Depósitos  a Prazo,  acordadas  entre o CRÉDITO  AGRÍCOLA  e  o(s) Titular(es) também ele(s) identificado(s) nessa mesma Ficha. </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 1.2.  As  presentes  Condições  Gerais,  em  conjunto  com  a  Ficha de  Informação  Normalizada  e  a  Ficha  de  Constituição  regulam a  constituição,  movimentação  e  encerramento  do  Produto  de Poupança  e/ou  Depósito  a  Prazo,  doravante  designado abreviadamente por Depósito.   </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 1.3.  A  constituição  do  Depósito  fica  dependente  da disponibilização  ao(s)  seu(s)  Titular(es)  das  presentes Condições  Gerais,  da  Ficha  de  Constituição  (FC)  e  da  Ficha Informação  Normalizada  (FIN)  e  condicionada  à  abertura  de uma  conta  de  Depósitos  à  Ordem,  à  qual  o  presente  Depósito ficará associado.   </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Identificação  do(s)  Titular(es)  /  Representante(s)  / Procurador(es)</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">2.1.  Salvo  instruções  expressas  em  contrário  e independentemente  de  quem  procedeu  à  abertura  do presente  Depósito,  a  sua  titularidade  é  igual  à  da  Conta  de Depósito à Ordem a ele associada.   </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 2.2.  Em  consequência  e  salvo  instruções  expressas  em contrário,  as  assinaturas  que  constam  na  Ficha  de Assinaturas  e  Abertura  de  Conta  de  Depósito  à  Ordem associada  a  este  Depósito,  bem  como  a  forma  de movimentação  daquela  referida  conta  são  válidas  para  a movimentação  e  encerramento  do  presente  Depósito, independentemente de quem procedeu à sua constituição.   </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;"> Correspondência e Comunicações</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">3.1.  Toda  a  correspondência  que  deva  ser  enviada  ao(s) Titular(es)  do  Depósito,  incluindo  a  relativa  a  citações judiciais,  considera-se  devidamente  efectuada  e  eficaz quando  seja  dirigida  para  o  último  endereço  por  ele(s) indicado  na  Ficha  de  Assinaturas  e  de  Abertura  de  Conta  de Depósito  à  Ordem,  e  decorridos  que  estejam  três  dias  após  a data de expedição.   </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 3.2.  A  Caixa  Agrícola  não  poderá  ser  responsabilizada  pelo extravio  de  qualquer  documento  ou  por  algum  prejuízo decorrente  desse  extravio  ou  utilização  abusiva  do  mesmo, quando  tenha  dirigido  o  envio  para  o  último  endereço  indicado pelo(s) Titular(es).   </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> 3.3.  Havendo  vários  Titulares  e  salvo  o  que  em  contrário possa  resultar  imperativamente  da  lei,  as  comunicações  da  Caixa  Agrícola  consideram-se  validamente  efectuadas  quando o sejam a qualquer um dos Titulares.    </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 3.4.  Sem  prejuízo  do  expresso  nos  números  anteriores,  as partes  poderão  efectuar  as  suas  recíprocas  comunicações através  de  correio  electrónico,  sendo  válido  para  tanto,  no c a s o   d a   C a i x a   A g r í c o l a   o   e n d e r e ç o linhadirecta@creditoagricola.pt  e  no  caso  do(s)  Titular(es) qualquer  um  dos  endereços  que  haja  sido  indicado  na  Ficha de Assinatura e de Abertura de Conta de Depósito à Ordem.     </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Constituição</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">4.1.  O  Depósito  é  representado  por  um  título  nominativo representativo  do  depósito  e  não  transmissível  por  acto  entre vivos. </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 4.2.  A  emissão  de  uma  segunda  via  do  título  representativo  a que  se  refere  o  número  anterior  dependerá  de  pedido fundamentado  subscrito  por  todos  os  Titulares,  ainda  que  o regime de movimentação seja o da solidariedade. </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 4.3.  O  presente  Depósito  rege-se  pelo  disposto  nas  presentes Condições  Gerais  e,  no  particular,  no  disposto  nas respectivas FIN e FC.  </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Termo e Mobilização</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">5.1.  O  Depósito  é  exigível  no  fim  do  prazo  por  que  foi constituído,  podendo,  todavia,  a  Caixa  Agrícola  conceder  a  sua mobilização  antecipada,  nas  condições  acordadas,  por  meio de  ordens  de  transferência,  autorizações  de  débito  ou quaisquer  outros  meios  permitidos  pela  Caixa  Agrícola,  desde que observado o regime de movimentação estabelecido. </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 5.2.  Não  sendo  o  Depósito  mobilizável  antecipadamente apenas  é  exigível  no  fim  do  prazo  por  que  foi  constituído,  não podendo  ser  reembolsado  antes  do  decurso  desse  mesmo prazo. </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> 5.3.  Salvo  prévia  indicação  escrita  da  Caixa  Agrícola  ou  do(s) Titular(es)  em  contrário,  o  Depósito,  quer  seja  mobilizável antecipadamente  ou  não,  renova-se  automaticamente  por prazo  igual  ao  inicialmente  acordado  e  à  taxa  que  então  estiver em vigor. </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Extracto</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">6.1.  A  Caixa  Agrícola  disponibilizará  ao(s)  Titular(es),  com periodicidade  mínima  anual  nos  Depósitos  com  prazo  inicial superior  a  um  (1)  ano  ou  na  data  do  respectivo  vencimento nos  Depósitos  com  prazo  inicial  inferior  a  um  (1)  ano,  um extracto  da  conta  com  todos  os  movimentos,  a  débito  e  a crédito,  respeitantes  a  esse  período,  sendo  que,  no  caso  de contas  colectivas,  o  extracto  será  disponibilizado exclusivamente ao primeiro Titular. </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;"> <br> B. DISPOSIÇÕES FINAIS</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Alterações</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">7.1.  A  Caixa  Agrícola  poderá  alterar,  na  renovação,  as  condições vigentes  à  data  da  contratação  de  Depósito  com  prazo determinado,  mediante  pré-aviso  ao(s)  Titular(es)  com  uma antecedência  suficiente  para  o  exercício,  por  parte  deste(s),  da oposição  à  renovação,  considerando-se  as  alterações  aceites, caso  o(s)  Titular(es)  não  manifeste(m),  até  à  data  da  renovação, oposição às mesmas.   </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 7.2.  Toda  e  qualquer  alteração  deverá  revestir  a  forma  escrita  e  ser efectuada nos termos do disposto supra na cláusula terceira (3).   </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Utilização e Protecção de Dados Pessoais</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">8.1.  O  segredo  bancário  respeitante  às  relações  entre  a  Caixa Agrícola e o(s) Titular(es) será protegido nos termos da lei.</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 8.2.  O(s)  Titular(es)  do  Depósito  bem  como  o(s)  seu(s) representante(s)  autorizam  a  Caixa  Agrícola  a  proceder  ao tratamento  informático  dos  dados  por  eles  fornecidos  no  âmbito da  relação  estabelecida  com  o  Grupo  Crédito  Agrícola,  podendo  a Caixa  Agrícola,  sem  prejuízo  do  cumprimento  do  dever  de  sigilo bancário,  proceder  ao  cruzamento  dessa  informação  com  a informação  fornecida  às  demais  entidades  do  Grupo  Crédito Agrícola.  Esta  autorização  compreende  a  utilização  da  informação recolhida  para  fins  de  natureza  estatística,  ou  para  identificação  de produtos  bancários  e  financeiros  do  Grupo  Crédito  Agrícola,  que sejam  susceptíveis  de  ser  do  interesse  do(s)  Titular(es)  e/ou  do(s) seu(s) representante(s).   </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 8.3.  Sem  prejuízo  do  dever  de  segredo  bancário,  o(s)  Titular(es) e/ou  o(s)  seu(s)  representante(s)  autoriza(m)  a  Caixa  Agrícola  a recolher  outras  informações  a  seu  respeito,  nomeadamente  junto do  Banco  de  Portugal  ou  de  outras  fontes,  no  âmbito  do  normal desenvolvimento da presente relação comercial.   </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 8.4.  Ao(s)  Titular(es)  assiste  sempre  o  direito,  nos  termos  da  lei, de  consulta  dos  seus  dados,  com  vista  à  sua  eventual  correcção, aditamento  ou  supressão,  o  qual  poderá  ser  exercido  por  contacto pessoal ou por escrito.   </td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;"> <br> 8.5.  O(s)  Titular(es)  autoriza(m)  expressamente  e  sem  reservas  a Caixa  Agrícola  a  transmitir  à  Caixa  Central  -  Caixa  Central  de Crédito  Agrícola  Mútuo,  CRL  e  às  restantes  Caixas  Agrícolas pertencentes  ao  Sistema  Integrado  do  Crédito  Agrícola  Mútuo (SICAM)  informações  sobre  a  titularidade,  movimentos  e  saldo  de qualquer  uma  das  contas  por  ele(s)  detidas  na  Caixa  Agrícola,  por forma  a  que,  em  cada  momento,  qualquer  Caixa  Agrícola  e/ou  A Caixa  Central  possa  dispor  desses  elementos,  autorizando, também  e  nomeadamente,  a  transmissão  desses  elementos  às autoridades  competentes  que  o  solicitem,  ficando  essas  trocas  de informação excluídas do dever de sigilo bancário.   </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Omissões</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">9.  Em  tudo  o  que  não  contrarie  as  presentes  Condições  Gerais, dão-se  aqui  por  reproduzidas  as  Condições  Gerais  do  Contrato de  Depósito,  as  quais  se  aplicam,  com  as  necessárias adaptações e no omisso, ao presente Depósito. </td>
        </tr>
        
        <tr>
            <td style="height: 30px;"></td>
        </tr>
        
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify; font-weight: bold;">Legislação e Foro Judicial</td>
        </tr>
        <tr>
            <td style="padding-left: 10px; width: 100%; text-align: justify;">10.  As  presentes  Condições  Gerais  regem-se  pelo  disposto  na legislação  portuguesa  e  para  resolução  de  qualquer  questão emergente  do  presente  contrato,  é  competente  o  foro  da Comarca  da  sede  da  Caixa  Agrícola  com  expressa  renúncia  a qualquer outro. </td>
        </tr>
        
    </table>
    
</page>
