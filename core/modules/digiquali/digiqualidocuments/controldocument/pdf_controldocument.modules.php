<?php
/* Copyright (C) 2025-2026 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 * \file    core/modules/digiqualidocuments/controldocument/pdf_controldocument.modules.php
 * \ingroup digiquali
 * \brief   File of class to generate control document pdf
 */

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

// Load Saturne libraries
require_once __DIR__ . '/../../../../../../saturne/core/modules/saturne/modules_saturne.php';

/**
 * Class to build control document pdf
 */
class pdf_controldocument extends SaturneDocumentModel
{
    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * @var string model name
     */
    public $name;

    /**
     * @var string model description (short text)
     */
    public $description;

    /**
     * @var string Module
     */
    public string $module = 'digiquali';

    /**
     * @var string Document type
     */
    public string $document_type = 'controldocument';

    /**
     *  Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs;

        parent::__construct($db, $this->module, $this->document_type);

        $this->name        = 'controldocument';
        $this->description = $langs->trans('ControlDocumentPDFDescription');
        $this->type        = 'pdf';
        $this->height      = 10;
    }

    /**
     *  Show top header of page
     *
     *  @param	TCPDF		$pdf     		Object PDF
     *  @param  Control		$object     	Object to show
     *  @param  Sheet		$object     	Object to show
     *  @param  Project		$object     	Object to show
     *  @param  Translate	$outputlangs	Object lang for output
     *  @return	float|int                   Return topshift value
     */
    protected function _pagehead(&$pdf, $object, $sheet, $project, $outputLangs, $defaultFontSize)
    {
        global $langs;

        $objectsMetadata = saturne_get_objects_metadata();
        $object->fetchObjectLinked('', '', $object->id, 'digiquali_control');
        $linkedObjectType = key($object->linkedObjects);

        $linkedElement = json_decode($sheet->element_linked, true);
        $linkedElement = array_keys($linkedElement)[0];
        if ($linkedElement == 'productlot') {
            $linkedElement = 'batch';
        }

        foreach ($objectsMetadata as $objectMetadata) {
            if ($objectMetadata['conf'] == 0 || $objectMetadata['link_name'] != $linkedObjectType) {
                continue;
            }
            $linkedObject = $object->linkedObjects[$objectMetadata['link_name']][key($object->linkedObjects[$objectMetadata['link_name']])];
        }

        $widthFirstColumn  = 35;
        $widthSecondColumn = 80;
        $widthThirdColumn  = 75;

        $verdict = '';
        if ($object->verdict == 1) {
            $verdict = 'OK';
        } elseif ($object->verdict == 2) {
            $verdict = 'KO';
        }
        $multdirOutput = getMultidirOutput($object, $object->module);
        $path          = $multdirOutput . '/control/' . $object->ref . '/photos';
        $thumb         = saturne_get_thumb_name($object->photo, 'medium', $path);
        $image         = $path . '/thumbs/' . $thumb;

        $data = [
            'ref' => [
                'type'   => 'rowThreeCols',
                'height' => $this->height,
                'cols'   => [
                    [
                        'width' => $widthFirstColumn,
                        'text'  => $langs->transnoentities('Ref') . $langs->transnoentities('Control'),
                        'align' => 'L',
                    ],
                    [
                        'width' => $widthSecondColumn,
                        'text'  => $object->ref,
                        'align' => 'L',
                    ],
                    [
                        'width'  => $widthThirdColumn,
                        'image'  => !empty($object->photo) ? $image : null,
                        'height' => 60,
                    ],
                ],
            ],
            'object' => [
                'type'   => 'rowTwoCols',
                'height' => $this->height,
                'cols'   => [
                    [
                        'width' => $widthFirstColumn,
                        'text'  => $langs->transnoentities('ControlObject'),
                    ],
                    [
                        'width' => $widthSecondColumn,
                        'text'  => $langs->transnoentities(ucfirst($linkedElement)) . ' : ' . $linkedObject->$linkedElement,
                    ],
                ],
            ],
            'date' => [
                'type'   => 'rowTwoCols',
                'height' => $this->height,
                'cols'   => [
                    [
                        'width' => $widthFirstColumn,
                        'text'  => $langs->transnoentities('ControlDate'),
                    ],
                    [
                        'width' => $widthSecondColumn,
                        'text'  => dol_print_date($object->control_date, 'day'),
                    ],
                ],
            ],
            'project' => [
                'type'   => 'rowTwoCols',
                'height' => $this->height,
                'cols'   => [
                    [
                        'width' => $widthFirstColumn,
                        'text'  => $langs->transnoentities('Project'),
                    ],
                    [
                        'width' => $widthSecondColumn,
                        'text'  => $project->title,
                    ],
                ],
            ],
            'sheet' => [
                'type'   => 'rowTwoCols',
                'height' => $this->height,
                'cols'   => [
                    [
                        'width' => $widthFirstColumn,
                        'text'  => $langs->transnoentities('Ref') . $langs->transnoentities('Sheet'),
                    ],
                    [
                        'width' => $widthSecondColumn,
                        'text'  => $sheet->ref . ' - ' . $sheet->label,
                    ],
                ],
            ],
            'verdict' => [
                'type'   => 'rowMerged',
                'height' => $this->height,
                'bg'     => [153, 204, 204],
                'bold'   => true,
                'label'  => $langs->transnoentities('VerdictObject'),
                'value'  => $verdict,
            ],

            'note' => [
                'type'   => 'text',
                'height' => $this->height,
                'text'   => $langs->transnoentities('NoteControl') . ': ' . $object->note_public,
            ],
        ];
        foreach ($data as $row) {
            switch ($row['type']) {
                case 'rowTwoCols':
                case 'rowThreeCols':
                    foreach ($row['cols'] as $col) {
                        // Police
                        if (!empty($col['bold'])) {
                            $pdf->SetFont('', 'B', $defaultFontSize);
                        } else {
                            $pdf->SetFont('', '', $defaultFontSize - 2);
                        }
                        // Image
                        if (!empty($col['image'])) {
                            $x = $pdf->GetX();
                            $y = $pdf->GetY();
                            $pdf->Image($col['image'], $x, $y, $col['width'], 0);
                            $pdf->Cell($col['width'], $col['height'], '', 1);
                        } else {
                            $pdf->Cell($col['width'], $row['height'], $col['text'] ?? '', 1, 0, $col['align'] ?? 'L');
                        }
                    }
                    $pdf->Ln($row['height']);
                    break;

                case 'rowMerged':
                    if (!empty($row['bg'])) {
                        $pdf->SetFillColor($row['bg'][0], $row['bg'][1], $row['bg'][2]);
                    }
                    $pdf->SetFont('', 'B', $defaultFontSize);
                    $totalWidth = $widthFirstColumn + $widthSecondColumn;

                    $pdf->Cell($totalWidth, $row['height'], $row['label'], 1, 0, 'C', true);
                    $pdf->Cell($widthThirdColumn, $row['height'], $row['value'], 1, 1, 'C', true);
                    $pdf->Ln(5);
                    break;

                case 'text':
                    $pdf->SetFont('', '', $defaultFontSize);
                    $pdf->Cell(0, $row['height'], strip_tags($row['text']), 0, 1, 'L');
                    $pdf->Ln(2);
                    break;
            }
        }
    }

    /**
     *  Add a page in the pdf if the height is between two pages
     *
     * @param Object $pdf
     * @param float $neededHeight
     */
    public function checkPageBreak($pdf, $neededHeight) {
        $bottomMargin = $pdf->getBreakMargin();
        $pageHeight   = $pdf->getPageHeight();
        $currentY     = $pdf->GetY();

        if ($currentY + $neededHeight + $bottomMargin > $pageHeight) {
            $pdf->AddPage();
        }
    }

    private function renderSignatureTable($pdf, $langs, $signatures, $title, $roles, $widthArray, $default_font_size)
    {
        $pdf->SetFont('', 'B', $default_font_size);
        $pdf->Cell(0, 8, $langs->transnoentities($title), 0, 1, 'L');
        $pdf->Ln(3);

        [$widthName, $widthPre, $widthDate, $widthSign] = $widthArray;

        //Header
        $dataSignatureHeader = [
            [
                'width' => $widthName,
                'html'  => '<div style="text-align:center;"><span style="font-size:10pt;">' . $langs->transnoentities('LastName') . '</span></div>',
            ],
            [
                'width' => $widthPre,
                'html'  => '<div style="text-align:center;"><span style="font-size:10pt;">' . $langs->transnoentities('FirstName') . '</span></div>',
            ],
            [
                'width' => $widthDate,
                'html'  => '<div style="text-align:center;">' . $langs->transnoentities('SignatureDate') . '</div>',
            ],
            [
                'width' => $widthSign,
                'html'  => '<div style="text-align:center;">' . $langs->transnoentities('Signature') . '</div>',
            ]
        ];
        $maxHeight = $this->calculateHeaderArraySize($pdf, $dataSignatureHeader);
        $this->checkPageBreak($pdf, $this->height);
        $pdf->SetFillColor(153, 204, 204);
        foreach ($dataSignatureHeader as $header) {
            $pdf->writeHTMLCell($header['width'], $maxHeight, '', '', $header['html'], 1, 0, true, true, 'C', true);
        }
        $pdf->Ln();

        // Content
        $pdf->SetFont('', '', $default_font_size);
        $found = false;

        foreach ($signatures as $signature) {
            if (!in_array($signature->role, $roles)) {
                continue;
            }
            $found = true;
            $heightLastName  = $pdf->getStringHeight($widthName,  $signature->lastname);
            $heightFirstName = $pdf->getStringHeight($widthPre,   $signature->firstname);
            $heightDate      = $pdf->getStringHeight($widthDate,  $signature->signature_date);
            $height          = max($heightLastName, $heightFirstName, $heightDate, 20);
            $this->checkPageBreak($pdf, $height);
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // Cells
            $pdf->MultiCell($widthName, $height, $signature->lastname, 1, 'C', 0, 0, $x, $y, true, 0, false, true, $height, 'M', false);
            $pdf->MultiCell($widthPre, $height, $signature->firstname, 1, 'C', 0, 0, $x + $widthName, $y, true, 0, false, true, $height, 'M', false);
            $offset = $widthName + $widthPre;
            $pdf->MultiCell($widthDate, $height, dol_print_date($signature->signature_date, 'day'), 1, 'C', 0, 0, $x + $offset, $y, true, 0, false, true, $height, 'M', false);
            $offset += $widthDate;
            // Signature
            if (!empty($signature->signature)) {
                $encoded = explode(",", $signature->signature)[1];
                $img     = base64_decode($encoded);

                $pdf->Image('@' . $img, $x + $offset, $y, $widthSign, $height, 'PNG', '', 'C', false, 300, '', false, false, 1);
                $pdf->SetXY($x + $offset, $y);
                $pdf->Cell($widthSign, $height, '', 1);
            } else {
                $pdf->MultiCell($widthSign, $offset, $langs->transnoentities('NA'), 1, 'C', 0, 0, $x + $offset, $y, true, 0, false, true, $height, 'M', false);
            }
            $pdf->Ln($height);
        }

        if (!$found) {
            $total = array_sum($widthArray);
            $pdf->Cell($total, 8, $langs->transnoentities('NoData'), 1, 1, 'C');
        }
    }


    /**
     *  Calcul the max header height size
     *
     * @param PDF $pdf           pdf object
     * @param Array $headerArray array containing header information width and html code
     * @return int               maxHeight=OK, <0=KO
     */
    private function calculateHeaderArraySize($pdf, $headerArray) {
        if (!is_array($headerArray) || empty($headerArray)) {
            return -1;
        }
        foreach ($headerArray as $header) {
            $headerHeight[] = $pdf->getStringHeight($header['width'], strip_tags($header['html']));
        }
        $maxHeight = max($headerHeight);

        return $maxHeight;

    }

    /**
     *  Write the PDF file to disk
     *
     * @param Object $object Object to generate (ex: control)
     * @param Translate $outputLangs Lang object
     * @param string $srctemplatepath
     * @param int $hidedetails
     * @param int $hidedesc
     * @param int $hideref
     * @param null|array $moreparams
     * @return int                         1=OK, <0=KO
     */
    public function write_file($objectDocument, $outputLangs, $srcTemplatePath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = array()): int
    {
        global $action, $langs, $hookmanager, $user;

        $id = GETPOST('id');

        $moreparams['hideTemplateName'] = 1;
        $file = $this->buildDocumentFilename($objectDocument, $outputLangs, $moreparams['object'], $moreparams);
        if ($file < 0) {
            $this->error = $langs->transnoentities('ErrorFileNameCanNotBeBuilt');
            return -1;
        }

        $hookmanager->initHooks(['pdfgeneration']);
        $parameters = ['file' => $file, 'object' => $moreparams['object'], 'outputlangs' => $outputLangs];
        $hookmanager->executeHooks('beforePDFCreation', $parameters, $moreparams['object'], $action); // Note that $action and $object may have been modified by some hooks

        // Create pdf instance
        $pdf              = pdf_getInstance($this->format);
        $defaultFontSize  = pdf_getPDFFontSize($outputLangs); // Must be after pdf_getInstance
        $defaultFontSize += 2;

        if (class_exists('TCPDF')) {
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
        }
        $pdf->SetFont(pdf_getPDFFont($outputLangs));

        $pdf->Open();
        $pdf->SetDrawColor(128, 128, 128);

        $pdf->SetTitle($outputLangs->convToOutputCharset($this->document_type));
        $pdf->SetSubject($outputLangs->transnoentities($this->document_type));
        $pdf->SetCreator('Dolibarr ' . DOL_VERSION);
        $pdf->SetAuthor($outputLangs->convToOutputCharset($user->getFullName($outputLangs)));
        $pdf->SetKeyWords($outputLangs->convToOutputCharset($moreparams['object']->ref) . ' ' . $outputLangs->transnoentities($this->document_type));

        $pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);

        $sheet            = new Sheet($this->db);
        $questions        = new Question($this->db);
        $controlDet       = new ControlLine($this->db);
        $users            = new User($this->db);
        $project          = new Project($this->db);
        $saturneSignature = new SaturneSignature($this->db);
        $controlEquipment = new ControlEquipment($this->db);
        $productLot       = new Productlot($this->db);

        $sheet->fetch($moreparams['object']->fk_sheet);
        $questions->fetchObjectLinked($sheet->id, 'digiquali_sheet', null, 'digiquali_question');
        $users->fetch($moreparams['object']->fk_user_controller);
        $project->fetch($moreparams['object']->projectid);
        $signatures        = $saturneSignature->fetchSignatories($moreparams['object']->id, $moreparams['object']->element);
        $controlEquipments = $controlEquipment->fetchFromParent($moreparams['object']->id);

        if (!empty($questions->linkedObjects)) {
            foreach ($questions->linkedObjects['digiquali_question'] as $question) {
                $controlDets = $controlDet->fetchFromParentWithQuestion($id, $question->id);
                foreach ($controlDets as $data) {
                    $answerRef[] = $data->ref;
                    $answer[]    = $data->answer;
                }
            }
        }
        $pdf               = pdf_getInstance($this->format);
        $default_font_size = pdf_getPDFFontSize($outputLangs);
        $default_font_size -= -2;

        $pdf->SetAutoPageBreak(1, $this->marge_basse);
        $pdf->setX(($this->marge_gauche + $this->marge_droite) / 2);

        if (class_exists("TCPDF")) {
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
        }

        $pdf->AddPage();
        $pdf->SetFont(pdf_getPDFFont($outputLangs), '', $default_font_size);

        $this->_pagehead($pdf, $moreparams['object'], $sheet, $project, $outputLangs, $default_font_size); // pdf header

        $pdf->SetFont('', 'B', $defaultFontSize);
        $pdf->Cell(0, 8, $langs->transnoentities('ControlObservationList'), 0, 1, 'L');
        $pdf->Ln(5);

        $pdf->SetFont('', 'B', $default_font_size);
        $pdf->SetFillColor(153, 204, 204);

        $widthQuestion    = 20;
        $widthTitle       = 80;
        $widthRefAnswer   = 20;
        $widthObservation = 40;
        $widthAnswer      = 30;
        $totalWidth       = $widthQuestion + $widthTitle + $widthRefAnswer + $widthObservation + $widthAnswer;
        $controlLine      = 0;

        $dataQuestionsHeader = [
            [
                'width' => $widthQuestion,
                'html'  => '<b>' . $langs->transnoentities('Ref') . '</b><br><span style="font-size:8px;">' . $langs->transnoentities('Question') . '</span>',
            ],
            [
                'width' => $widthTitle,
                'html'  => '<b>' . $langs->transnoentities('Title') . ' - ' . $langs->transnoentities('Description') . '</b>',
            ],
            [
                'width' => $widthRefAnswer,
                'html'  => '<b>' . $langs->transnoentities('Ref') . '</b><br><span style="font-size:8px;">' . $langs->transnoentities('Answer') . '</span>',
            ],
            [
                'width' => $widthObservation,
                'html'  => '<b>' . $langs->transnoentities('Observations') . '</b>',
            ],
            [
                'width' => $widthAnswer,
                'html'  => '<b>' . $langs->transnoentities('Answer') . '</b>',
            ],
        ];

        $maxHeight = $this->calculateHeaderArraySize($pdf, $dataQuestionsHeader);
        foreach ($dataQuestionsHeader as $col) {
            $pdf->writeHTMLCell($col['width'], $maxHeight, '', '', $col['html'], 1, 0, true, true, 'C');
        }

        $pdf->Ln($maxHeight);

        $multdirOutput = getMultidirOutput($moreparams['object'], $moreparams['object']->module);
        if (!empty($questions->linkedObjects['digiquali_question'])) {
            foreach ($questions->linkedObjects['digiquali_question'] as $question) {
                $photoPath = $multdirOutput . '/control/' . $moreparams['object']->ref . '/answer_photo/' . $question->ref;
                if (is_dir($photoPath)) {
                    $photoFiles = array_values(array_diff(scandir($photoPath), ['.', '..']));
                    foreach ($photoFiles as $photoFile) {
                        $fullPath = realpath($photoPath . '/' . $photoFile);
                        if ($fullPath && file_exists($fullPath) && is_readable($fullPath) && $photoFile != 'thumbs') {
                            $photo = saturne_get_thumb_name($photoFile, 'small', $photoPath);
                            $questionPhotos[] = $photoPath . '/thumbs/' . $photo;
                        }
                    }
                }
                $heightRef        = $pdf->getStringHeight($widthQuestion, $question->ref);
                $heightTitle      = $pdf->getStringHeight($widthTitle, $question->label . ' ' . html_entity_decode(strip_tags($question->description)));
                $heightRefAnswer  = $pdf->getStringHeight($widthRefAnswer, $answerRef[$controlLine]);
                $heightObs        = $pdf->getStringHeight($widthObservation, $moreparams['object']->lines[$controlLine]->comment);
                $heightAns        = $pdf->getStringHeight($widthAnswer, $answer[$controlLine]);
                $lineHeight       = max([$heightRef, $heightTitle, $heightRefAnswer, $heightObs, $heightAns]);

                $this->checkPageBreak($pdf, $lineHeight);
                $x = $pdf->GetX();
                $y = $pdf->GetY();

                $pdf->MultiCell($widthQuestion, $lineHeight, $question->ref, 1, 'C', 0, 0, $x, $y, true, 0, false, true, $lineHeight, 'M', true);
                $pdf->MultiCell($widthTitle, $lineHeight, $question->label . ' ' . html_entity_decode(strip_tags($question->description)), 1, 'L', 0, 0, $x + $widthQuestion, $y, true, 0, false, true, $lineHeight, 'M', true);
                $pdf->MultiCell($widthRefAnswer, $lineHeight, $answerRef[$controlLine], 1, 'C', 0, 0, $x + $widthQuestion + $widthTitle, $y, true, 0, false, true, $lineHeight, 'M', true);
                $pdf->MultiCell($widthObservation, $lineHeight, $moreparams['object']->lines[$controlLine]->comment, 1, 'L', 0, 0, $x + $widthQuestion + $widthTitle + $widthRefAnswer, $y, true, 0, false, true, $lineHeight, 'M', true);
                $pdf->MultiCell($widthAnswer, $lineHeight, $answer[$controlLine], 1, 'C', 0, 0, $x + $widthQuestion + $widthTitle + $widthRefAnswer + $widthObservation, $y, true, 0, false, true, $lineHeight, 'M', true);

                $pdf->Ln($lineHeight);

                $photoHeight = 25;
                $this->checkPageBreak($pdf, $photoHeight);

                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->MultiCell($totalWidth, $photoHeight, '', 1, 'C');

                if (!empty($questionPhotos)) {
                    $maxPerLine = 4;
                    $imgWidth   = ($totalWidth - 10) / $maxPerLine;
                    $imgHeight  = $photoHeight - 6;
                    $posX       = $x + 3;
                    $posY       = $y + 3;
                    $i          = 0;
                    foreach ($questionPhotos as $img) {
                        clearstatcache();
                        if (file_exists($img) && str_contains($img, $question->ref)) {
                            $pdf->Image($img, $posX, $posY, $imgWidth, $imgHeight, '', '', '', false, 300);
                            $posX += $imgWidth + 2;
                            $i++;
                            if ($i >= $maxPerLine) {
                                break;
                            }
                        }
                    }
                } else {
                    $pdf->SetXY($x, $y);
                    $pdf->MultiCell($totalWidth, $photoHeight, $langs->transnoentities('NoPhotoYet'), 0, 'C', false, 1, null, null, true, 0, false, true, $photoHeight, 'M', false);
                }
                $pdf->Ln(3);
                $controlLine++;
            }
        }

        $pdf->SetFont('', '', $default_font_size);

        $pdf->Ln(10);
        $pdf->SetFont('', 'B', $default_font_size);
        $pdf->Cell(0, 8, $langs->transnoentities('ControlEquipementList'), 0, 1, 'L');
        $pdf->Ln(3);

        $widthRef  = 25;
        $widthLib  = 35;
        $widthLot  = 30;
        $widthDesc = 50;
        $widthDluo = 20;
        $widthRest = 30;

        $dataEquipmentsHeader = [
            [
                'width' => $widthRef,
                'html'  => '<div style="text-align:center;"><span style="font-size:10pt;">' . $langs->transnoentities('Ref') . '</span><br><span style="font-size:8pt;">' . $langs->transnoentities('Equipement') . '</span></div>',
            ],
            [
                'width' => $widthLib,
                'html'  => '<div style="text-align:center;"><span style="font-size:10pt;">' . $langs->transnoentities('Label') . '</span></div>',
            ],
            [
                'width' => $widthLot,
                'html'  => '<div style="text-align:center;"><span style="font-size:10pt;">' . $langs->transnoentities('Ref') . '</span><br><span style="font-size:8pt;">' . $langs->transnoentities('batch_number') . '</span></div>',
            ],
            [
                'width' => $widthDesc,
                'html'  => '<div style="text-align:center;"><span style="font-size:10pt;">' . $langs->transnoentities('Description') . '</span></div>',
            ],
            [
                'width' => $widthDluo,
                'html'  => '<div style="text-align:center;"><span style="font-size:10pt;">' . $langs->transnoentities('OptimalExpirationDate') . '</span></div>',
            ],
            [
                'width' => $widthRest,
                'html'  => '<div style="text-align:center;"><span style="font-size:10pt;">' . $langs->transnoentities('EstimatedLife') . '</span></div>',
            ],
        ];

        $maxHeight = $this->calculateHeaderArraySize($pdf, $dataEquipmentsHeader);
        foreach ($dataEquipmentsHeader as $col) {
            $pdf->writeHTMLCell($col['width'], $maxHeight, '', '', $col['html'], 1, 0, true, true, 'C', true);
        }

        $pdf->Ln();

        $pdf->SetFont('', '', $default_font_size);

        $dataEquipmentsRows = [];

        if (!empty($controlEquipments)) {
            foreach ($controlEquipments as $controlEquipement) {
                $equipement = json_decode($controlEquipement->json);
                $productLot->fetch($controlEquipement->fk_lot);
                $dataEquipmentsRows[] = [
                    'ref'         => $controlEquipement->ref,
                    'label'       => $equipement->label,
                    'lot'         => $productLot->batch,
                    'description' => $equipement->description,
                    'dluo'        => dol_print_date($equipement->dluo),
                    'lifetime'    => $equipement->lifetime,
                ];
            }
        }

        $pdf->SetFont('', '', $default_font_size);

        if (!empty($dataEquipmentsRows)) {
            foreach ($dataEquipmentsRows as $row) {
                // Calcul hauteurs
                $heightRef         = $pdf->getStringHeight($widthRef,  $row['ref']);
                $heightLabel       = $pdf->getStringHeight($widthLib,  $row['label']);
                $heightLot         = $pdf->getStringHeight($widthLot,  $row['lot']);
                $heightDescription = $pdf->getStringHeight($widthDesc, $row['description']);
                $heightDluo        = $pdf->getStringHeight($widthDluo, $row['dluo']);
                $heightLifetime    = $pdf->getStringHeight($widthRest, $row['lifetime']);

                $height = max($heightRef, $heightLabel, $heightLot, $heightDescription, $heightDluo, $heightLifetime);

                $this->checkPageBreak($pdf, $height);

                $x = $pdf->GetX();
                $y = $pdf->GetY();

                $pdf->MultiCell($widthRef,  $height, $row['ref'],        1, 'C', 0, 0, $x, $y);
                $pdf->MultiCell($widthLib,  $height, $row['label'],      1, 'L', 0, 0, $x + $widthRef, $y);
                $pdf->MultiCell($widthLot,  $height, $row['lot'],        1, 'C', 0, 0, $x + $widthRef + $widthLib, $y);
                $pdf->MultiCell($widthDesc, $height, $row['description'],1, 'L', 0, 0, $x + $widthRef + $widthLib + $widthLot, $y);
                $pdf->MultiCell($widthDluo, $height, $row['dluo'],        1, 'C', 0, 0, $x + $widthRef + $widthLib + $widthLot + $widthDesc, $y);
                $pdf->MultiCell($widthRest, $height, $row['lifetime'],   1, 'C', 0, 0, $x + $widthRef + $widthLib + $widthLot + $widthDesc + $widthDluo, $y);
                $pdf->Ln($height);
            }
        } else {
            $pdf->Cell($widthRef + $widthLib + $widthLot + $widthDesc + $widthDluo + $widthRest, 8, $langs->transnoentities('NoEquipmentFound'), 1, 1, 'C');
        }

        $signatureWidths = [55, 55, 35, 45];
        $pdf->Ln(10);
        $this->renderSignatureTable($pdf, $langs, $signatures, 'LinkedContactsControl', ['ExtSocietyAttendant', 'Attendant'], $signatureWidths, $default_font_size);
        $pdf->Ln(10);
        $this->renderSignatureTable($pdf, $langs, $signatures, 'LinkedControllerControl', ['Controller'], $signatureWidths, $default_font_size);

        try {
            $pdf->Output($file, 'F');
        } catch (Exception $exception) {
            $this->error = "Erreur lors de la création du PDF : " . $exception->getMessage();
            return -1;
        }

        $this->result = ['fullpath' => $file];

        return 1;
    }
}

?>
