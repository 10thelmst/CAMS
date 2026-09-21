<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = get_cams_pdo();
$clientId = isset($_GET['client_id']) ? (int) $_GET['client_id'] : 0;

$client = null;
$cases = [];
$historySummary = [];

if ($clientId > 0) {
    $clientStmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
    $clientStmt->execute([':id' => $clientId]);
    $client = $clientStmt->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        $caseStmt = $pdo->prepare("SELECT * FROM concerns WHERE client_id = :client_id ORDER BY created_at DESC, id DESC LIMIT 10");
        $caseStmt->execute([':client_id' => $clientId]);
        $cases = $caseStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cases as $caseRow) {
            $statusStmt = $pdo->prepare("SELECT * FROM status_history WHERE concern_id = :concern_id ORDER BY changed_at DESC, id DESC LIMIT 5");
            $statusStmt->execute([':concern_id' => $caseRow['id'] ?? 0]);
            $statusRows = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
            $historySummary[] = [
                'case' => $caseRow,
                'status_history' => $statusRows
            ];
        }
    }
}

function safeValue($value) {
    return $value === null || $value === '' ? 'N/A' : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$fullName = $client ? trim(implode(' ', array_filter([
    $client['first_name'] ?? '',
    $client['middle_name'] ?? '',
    $client['last_name'] ?? '',
    $client['suffix'] ?? ''
], fn($part) => $part !== ''))) : 'Client';
$subject = $cases[0]['subject'] ?? 'No recent case';
$status = $cases[0]['status'] ?? 'Open';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CRM On Demand | Client History</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      background: #dfe7f4;
      font-family: Arial, Helvetica, sans-serif;
      color: #2b2f36;
    }
    .crm-shell {
      </script>
    </body>
    </html>
                      <?php foreach ($cases as $caseRow): ?>
                        <tr>
                          <td><?= safeValue($caseRow['ticket_number'] ?? '') ?></td>
                          <td><?= safeValue($caseRow['subject'] ?? '') ?></td>
                          <td><span class="badge <?= strtolower((string) ($caseRow['status'] ?? 'Open')) === 'closed' ? 'status-closed' : 'status-open' ?>"><?= safeValue($caseRow['status'] ?? 'Open') ?></span></td>
                          <td><?= safeValue($caseRow['created_at'] ?? '') ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="panel">
              <div class="panel-title">Recent Status Timeline</div>
              <div class="panel-body">
                <?php if (empty($historySummary)): ?>
                  <p class="mb-0">No status updates available.</p>
                <?php else: ?>
                  <table class="mini-table">
                    <thead>
                      <tr>
                        <th>Case</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Changed</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($historySummary as $entry): ?>
                        <?php foreach (($entry['status_history'] ?? []) as $statusEntry): ?>
                          <tr>
                            <td><?= safeValue($entry['case']['ticket_number'] ?? '') ?></td>
                            <td><?= safeValue($statusEntry['status'] ?? '') ?></td>
                            <td><?= safeValue($statusEntry['remarks'] ?? '') ?></td>
                            <td><?= safeValue($statusEntry['changed_at'] ?? '') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Edit client popup modal (loads edit_client.php in iframe) -->
  <div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Edit Client</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <iframe id="editClientIframe" src="about:blank" style="width:100%;height:70vh;border:0;"></iframe>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      var btn = document.getElementById('btn_edit_client');
      if (!btn) return;
      btn.addEventListener('click', function(){
        var id = this.dataset.clientId || '<?= htmlspecialchars($clientId, ENT_QUOTES) ?>';
        var iframe = document.getElementById('editClientIframe');
        iframe.src = 'edit_client.php?client_id=' + encodeURIComponent(id) + '&modal=1';
        var modal = new bootstrap.Modal(document.getElementById('editClientModal'));
        modal.show();
      });
    });
  </script>
</body>
</html>
                if (!data.ok){ body.innerHTML = '<div class="alert alert-danger">'+escapeHtml(data.message||'Failed to load')+'</div>'; return; }
                var c = data.client || {};
                body.innerHTML = `
                  <form id="editClientForm">
                    <input type="hidden" name="client_id" value="${escapeHtml(c.id||'')}">

                    <div class="panel-wrap">
                      <div class="panel-title">Edit: Client Personal Details</div>
                      <div class="panel-body">
                        <div class="detail-panel">
                          <div class="two-col" style="padding:8px 10px 0;">
                            <div class="detail-column">
                              <div class="detail-row"><div class="detail-label">Last Name <span class="text-danger">*</span></div><input class="editable-form-field" name="last_name" id="last_name_modal" value="${escapeHtml(c.last_name||'')}"></div>
                              <div class="detail-row"><div class="detail-label">First Name <span class="text-danger">*</span></div><input class="editable-form-field" name="first_name" id="first_name_modal" value="${escapeHtml(c.first_name||'')}"></div>
                              <div class="detail-row"><div class="detail-label">Middle Name</div><input class="editable-form-field" name="middle_name" id="middle_name_modal" value="${escapeHtml(c.middle_name||'')}"></div>
                              <div class="detail-row"><div class="detail-label">Suffix</div><select class="editable-form-field" name="suffix" id="suffix_modal"><option value="">--</option><option value="Jr.">Jr.</option><option value="Jra.">Jra.</option><option value="Sr.">Sr.</option><option value="II">II</option><option value="III">III</option><option value="IV">IV</option></select></div>
                              <div class="detail-row"><div class="detail-label">Contact Number</div><input class="editable-form-field" name="contact_no" id="contact_no_modal" value="${escapeHtml(c.contact_no||'')}"></div>
                              <div class="detail-row"><div class="detail-label">Gender</div><select class="editable-form-field" name="sex" id="sex_modal"><option value="">--</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                            </div>
                            <div class="detail-column">
                              <div class="detail-row"><div class="detail-label">Email Address</div><input class="editable-form-field" type="email" name="email" id="email_modal" value="${escapeHtml(c.email||'')}"></div>
                              <div class="detail-row"><div class="detail-label">Date of Birth</div><input class="editable-form-field" type="date" name="dob" id="dob_modal" value="${escapeHtml(c.dob||'')}"></div>
                              <div class="detail-row"><div class="detail-label">Address 1</div><input class="editable-form-field" name="address1" id="address1_modal" value="${escapeHtml(c.address1||'')}"></div>
                              <div class="detail-row"><div class="detail-label">Region</div><select class="editable-form-field" name="region_code" id="region_code_modal"><option>Loading...</option></select></div>
                              <div class="detail-row"><div class="detail-label">Province</div><select class="editable-form-field" name="province_code" id="province_code_modal" required disabled><option value="">-- Select Province --</option></select></div>
                              <div class="detail-row"><div class="detail-label">Town / City</div><select class="editable-form-field" name="city_code" id="city_code_modal" disabled><option value="">-- Select Town/City --</option></select></div>
                              <div class="detail-row"><div class="detail-label">Barangay</div><select class="editable-form-field" name="barangay_code" id="barangay_code_modal" disabled><option value="">-- Select Barangay --</option></select></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="panel-wrap mt-2">
                      <div class="panel-title">OFW Information</div>
                      <div class="panel-body">
                        <label class="crm-check"><input type="checkbox" id="is_ofw_modal" name="is_ofw" value="1"> <span>Client is the OFW himself/herself</span></label>
                        <div class="detail-panel">
                          <div class="two-col" style="padding:8px 10px 0;">
                            <div class="detail-column">
                              <div class="detail-row"><div class="detail-label">OFW Last Name</div><input class="editable-form-field" name="ofw_last_name" id="ofw_last_name_modal" value="${escapeHtml(c.ofw_last_name||'')}"></div>
                              <div class="detail-row"><div class="detail-label">OFW First Name</div><input class="editable-form-field" name="ofw_first_name" id="ofw_first_name_modal" value="${escapeHtml(c.ofw_first_name||'')}"></div>
                              <div class="detail-row"><div class="detail-label">OFW Middle Name</div><input class="editable-form-field" name="ofw_middle_name" id="ofw_middle_name_modal" value="${escapeHtml(c.ofw_middle_name||'')}"></div>
                            </div>
                            <div class="detail-column">
                              <div class="detail-row"><div class="detail-label">Relationship</div><select class="editable-form-field" name="relationship" id="relationship_modal"><option value="">-- Select Relationship --</option><option value="Self">Self</option><option value="Spouse">Spouse</option><option value="Child">Child</option><option value="Parent">Parent</option><option value="Sibling">Sibling</option><option value="Relative">Relative</option><option value="Representative">Representative</option></select></div>
                              <div class="detail-row"><div class="detail-label">Country</div><input class="editable-form-field" name="country" id="country_modal" value="${escapeHtml(c.country||'')}"></div>
                              <div class="detail-row"><div class="detail-label">Employment Type</div><select class="editable-form-field" name="employment_type" id="employment_type_modal"><option value="">-- Select Type --</option><option value="Land-based">Land-based</option><option value="Sea-based">Sea-based</option></select></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                `;

                // After injecting form, initialize selects and values
                function loadRegionsModal() {
                  fetch('../auth/ajax_address_json.php?action=get_regions')
                    .then(r=>r.json())
                    .then(regions=>{
                      var rs = document.getElementById('region_code_modal'); rs.innerHTML='';
                      regions.forEach(r=>{ var sel = (r.code=== (c.region_code||'05')) ? 'selected' : ''; rs.innerHTML += `<option value="${r.code}" ${sel}>${escapeHtml(r.name)}</option>`; });
                      var regionVal = document.getElementById('region_code_modal').value || '05';
                      loadProvincesModal(regionVal);
                    }).catch(()=>{});
                }

                function loadProvincesModal(region_code) {
                  var prov = document.getElementById('province_code_modal'); prov.innerHTML = '<option value="">-- Select Province --</option>';
                  prov.disabled = true;
                  fetch(`../auth/ajax_address_json.php?action=get_provinces&region_code=${region_code}`)
                    .then(r=>r.json()).then(list=>{
                      list.forEach(p=>{ prov.innerHTML += `<option value="${p.code}" ${p.code=== (c.province_code||'') ? 'selected' : ''}>${escapeHtml(p.name)}</option>`; });
                      prov.disabled = false;
                      if (c.province_code) loadCitiesModal(c.province_code); else { document.getElementById('city_code_modal').innerHTML='<option value="">-- Select Town/City --</option>'; document.getElementById('barangay_code_modal').innerHTML='<option value="">-- Select Barangay --</option>'; }
                    }).catch(()=>{});
                }

                function loadCitiesModal(province_code) {
                  var city = document.getElementById('city_code_modal'); city.innerHTML = '<option value="">-- Select Town/City --</option>'; city.disabled = true;
                  fetch(`../auth/ajax_address_json.php?action=get_cities&province_code=${province_code}`).then(r=>r.json()).then(list=>{
                    list.forEach(cc=>{ city.innerHTML += `<option value="${cc.code}" ${cc.code=== (c.city_code||'') ? 'selected' : ''}>${escapeHtml(cc.name)}</option>`; });
                    city.disabled = false;
                    if (c.city_code) loadBarangaysModal(c.city_code);
                  }).catch(()=>{});
                }

                function loadBarangaysModal(city_code) {
                  var brgy = document.getElementById('barangay_code_modal'); brgy.innerHTML = '<option value="">-- Select Barangay --</option>'; brgy.disabled = true;
                  fetch(`../auth/ajax_address_json.php?action=get_barangays&city_code=${city_code}`).then(r=>r.json()).then(list=>{
                    list.forEach(b=>{ brgy.innerHTML += `<option value="${b.code}" ${b.code=== (c.barangay_code||'') ? 'selected' : ''}>${escapeHtml(b.name)}</option>`; });
                    brgy.disabled = false;
                  }).catch(()=>{});
                }

                // wire up change handlers
                document.getElementById('region_code_modal').addEventListener('change', function(){ loadProvincesModal(this.value); });
                document.getElementById('province_code_modal').addEventListener('change', function(){ document.getElementById('city_code_modal').innerHTML='<option value="">-- Select Town/City --</option>'; document.getElementById('barangay_code_modal').innerHTML='<option value="">-- Select Barangay --</option>'; if (this.value) loadCitiesModal(this.value); });
                document.getElementById('city_code_modal').addEventListener('change', function(){ document.getElementById('barangay_code_modal').innerHTML='<option value="">-- Select Barangay --</option>'; if (this.value) loadBarangaysModal(this.value); });

                // set simple selects
                document.getElementById('suffix_modal').value = c.suffix || '';
                document.getElementById('sex_modal').value = c.sex || '';
                document.getElementById('relationship_modal').value = c.relationship || '';
                document.getElementById('employment_type_modal').value = c.employment_type || '';

                // OFW toggle
                var isOfwCb = document.getElementById('is_ofw_modal');
                if (isOfwCb) {
                  isOfwCb.checked = !!c.is_ofw;
                  function toggleOfwModal(){
                    var display = isOfwCb.checked ? false : true;
                    document.getElementById('ofw_last_name_modal').required = !isOfwCb.checked;
                    document.getElementById('ofw_first_name_modal').required = !isOfwCb.checked;
                  }
                  isOfwCb.addEventListener('change', toggleOfwModal);
                  toggleOfwModal();
                }

                // initialize region/province/city/barangay lists
                loadRegionsModal();
              })
              .catch(()=>{ body.innerHTML = '<div class="alert alert-danger">Failed to load client details.</div>'; });
          });

          document.getElementById('saveEditClientBtn').addEventListener('click', function(){
            var form = document.getElementById('editClientForm');
            if (!form) return;
            var fd = new FormData(form);
            fd.append('action','update_client');

            fetch('edit_client.php', { method: 'POST', body: fd })
              .then(r=>r.json())
              .then(resp=>{
                if (resp.ok){ editModal.hide(); location.reload(); return; }
                var body = document.getElementById('editClientModalBody');
                body.querySelectorAll('.alert').forEach(n=>n.remove());
                var err = document.createElement('div'); err.className='alert alert-danger'; err.textContent = resp.message || 'Update failed'; body.prepend(err);
              })
              .catch(()=>{
                var body = document.getElementById('editClientModalBody'); body.innerHTML = '<div class="alert alert-danger">Update request failed.</div>';
              });
          });

          // Inline save handler for client detail form
          document.getElementById('save_client_inline')?.addEventListener('click', function(){
            var f = document.getElementById('client_inline_edit_form');
            if (!f) return;
            var fd = new FormData(f);
            fd.append('action','update_client');

            fetch('edit_client.php', { method: 'POST', body: fd })
              .then(r=>r.json())
              .then(resp=>{
                if (resp.ok){ location.reload(); return; }
                alert(resp.message || 'Failed to save changes');
              })
              .catch(()=>{ alert('Update request failed'); });
          });
        })();
      </script>
    </body>
    </html>
