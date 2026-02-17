<?php

    class LD2460 extends IPSModule
    {
        
        public function Create() {
            //Never delete this line!
            parent::Create();


            
            $this->RegisterPropertyInteger('Targets', 1);
            $this->RegisterPropertyInteger('x1', 1);
            $this->RegisterPropertyInteger('y1', 1);
            $this->RegisterPropertyInteger('x2', 1);
            $this->RegisterPropertyInteger('y2', 1);
            $this->RegisterPropertyInteger('x3', 1);
            $this->RegisterPropertyInteger('y3', 1);
            $this->RegisterPropertyInteger('x4', 1);
            $this->RegisterPropertyInteger('y4', 1);
            $this->RegisterPropertyInteger('x5', 1);
            $this->RegisterPropertyInteger('y5', 1);    


            // Visualisierungstyp auf 1 setzen, da wir HTML anbieten möchten
            $this->SetVisualizationType(1);
        }

        public function ApplyChanges() {
            parent::ApplyChanges();

            // Aktualisiere registrierte Nachrichten
            foreach ($this->GetMessageList() as $senderID => $messageIDs) {
                foreach($messageIDs as $messageID) {
                    $this->UnregisterMessage($senderID, $messageID);
                }
            }

            foreach(['x1', 'y1', 'x2', 'y2', 'x3', 'y3', 'x4', 'y4', 'x5', 'y5'] as $counterProperty) {
                $this->RegisterMessage($this->ReadPropertyInteger($counterProperty), OM_CHANGENAME);
                $this->RegisterMessage($this->ReadPropertyInteger($counterProperty), VM_UPDATE);
            }

            // Schicke eine komplette Update-Nachricht an die Darstellung, da sich ja Parameter geändert haben können
            $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
        }

        public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
            // Man könnte noch auf weitere Nachrichten reagieren, um das ganze "vollständig" zu machen
            // Werden registrierte Objekte gelöscht? Aktualisiert sich das Bild? Da dies aber nur ein Beispiel ist, lasse ich diese Nachrichten weg
            foreach(['x1', 'y1', 'x2', 'y2', 'x3', 'y3', 'x4', 'y4', 'x5', 'y5'] as $index => $counterProperty) {
                if ($SenderID === $this->ReadPropertyInteger($counterProperty)) {
                    switch ($Message) {
                        case OM_CHANGENAME:
                            // Teile der HTML-Darstellung den neuen Namen mit
                            $this->UpdateVisualizationValue(json_encode([
                                'name' . ($index + 1) => $Data[0]
                            ]));
                            break;

                        case VM_UPDATE:
                            // Teile der HTML-Darstellung den neuen Wert mit. Damit dieser korrekt formatiert ist, holen wir uns den von der Variablen via GetValueFormatted
                          //  $this->UpdateVisualizationValue(json_encode([
                          //    'value' . ($index + 1) => GetValueFormatted($this->ReadPropertyInteger($counterProperty))
                          //  ]));

                            // Schicke eine komplette Update-Nachricht an die Darstellung, da sich ja Parameter geändert haben können
                            $this->UpdateVisualizationValue($this->GetFullUpdateMessage());
                            break;
                    }
                }
            }
        }

        
        public function GetVisualizationTile() {
            // Füge ein Skript hinzu, um beim laden, analog zu Änderungen bei Laufzeit, die Werte zu setzen
            // Obwohl die Rückgabe von GetFullUpdateMessage ja schon JSON-codiert ist wird dennoch ein weiteres mal json_encode ausgeführt
            // Damit wird dem String Anführungszeichen hinzugefügt und eventuelle Anführungszeichen innerhalb werden korrekt escaped
            $initialHandling = '<script>handleMessage(' . json_encode($this->GetFullUpdateMessage()) . ');</script>';

            // Füge statisches HTML aus Datei hinzu
            $module = file_get_contents(__DIR__ . '/module.html');

            // Gebe alles zurück. 
            // Wichtig: $initialHandling nach hinten, da die Funktion handleMessage ja erst im HTML definiert wird
            return $module . $initialHandling;
        }

        // Generiere eine Nachricht, die alle Elemente in der HTML-Darstellung aktualisiert
        //
        //Falls die Variablen noch nicht im LD2460 Device definiert siond führt das zu Fehleren.
        //Eigenlich müßte man bhier alle Variablen mit IPS_VariableExists($counter1ID) überprüfen
        //Ausweg: Fehler ignorieren, alle Variablen defoinieren und den Modul nochmal udaten
        //
        private function GetFullUpdateMessage() {
            $targetsID = $this->ReadPropertyInteger('Targets');
            $x1ID = $this->ReadPropertyInteger('x1');
            $y1ID = $this->ReadPropertyInteger('y1');
            $x2ID = $this->ReadPropertyInteger('x2');
            $y2ID = $this->ReadPropertyInteger('y2');
            $x3ID = $this->ReadPropertyInteger('x3');
            $y3ID = $this->ReadPropertyInteger('y3');
            $x4ID = $this->ReadPropertyInteger('x4');
            $y4ID = $this->ReadPropertyInteger('y4');
            $x5ID = $this->ReadPropertyInteger('x5');
            $y5ID = $this->ReadPropertyInteger('y5');

            $result['valueTargets'] = GetValueFormatted($targetsID);
            $result['x1'] = GetValueFormatted($x1ID);
            $result['y1'] = GetValueFormatted($y1ID);
            $result['x2'] = GetValueFormatted($x2ID);
            $result['y2'] = GetValueFormatted($y2ID);
            $result['x3'] = GetValueFormatted($x3ID);
            $result['y3'] = GetValueFormatted($y3ID);
            $result['x4'] = GetValueFormatted($x4ID);
            $result['y4'] = GetValueFormatted($y4ID);
            $result['x5'] = GetValueFormatted($x5ID);
            $result['y5'] = GetValueFormatted($y5ID);

            return json_encode($result);
        }
    
    }

?>
