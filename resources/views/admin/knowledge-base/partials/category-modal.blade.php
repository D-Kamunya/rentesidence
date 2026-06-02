{{-- Category Modal --}}
<div id="categoryModal" style="display: none; position: fixed; inset: 0; background: rgba(17,24,39,.45); backdrop-filter: blur(2px); z-index: 1050; align-items: center; justify-content: center;" 
     onclick="if(event.target === this) closeCategoryModal();">
    <div style="background: #fff; border-radius: 14px; max-width: 480px; width: 90%; margin: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        {{-- Modal Header --}}
        <div style="background: #fafafa; border-bottom: 0.5px solid #e5e7eb; padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; margin-bottom: 4px;">CATEGORY</div>
                    <h3 id="modalTitle" style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">Add Category</h3>
                </div>
                <button onclick="closeCategoryModal()" style="width: 30px; height: 30px; border-radius: 7px; background: #f3f4f6; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .13s;" 
                        onmouseover="this.style.background='#e5e7eb';" onmouseout="this.style.background='#f3f4f6';">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M3.5 3.5L10.5 10.5M10.5 3.5L3.5 10.5" stroke="#374151" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Modal Body --}}
        <form id="categoryForm" method="POST" style="padding: 20px;">
            @csrf
            <input type="hidden" name="_method" value="POST" id="formMethod">
            
            <div class="mb-3">
                <label for="name" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Name *</label>
                <input type="text" name="name" id="name" required 
                       style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                       onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                       onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
            </div>

            <div class="mb-3">
                <label for="icon" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Icon (optional)</label>
                <input type="text" name="icon" id="icon" placeholder="e.g., heroicon:book-open"
                       style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                       onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                       onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
            </div>

            <div class="mb-3">
                <label for="description" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Description</label>
                <textarea name="description" id="description" rows="3" 
                          style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; resize: vertical; transition: all .15s;"
                          onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                          onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';"></textarea>
            </div>

            <div class="mb-3">
                <label for="audience" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Audience *</label>
                <select name="audience" id="audience" required
                        style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; background: #fff; transition: all .15s;"
                        onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                    <option value="both">Both (Owners & Affiliates)</option>
                    <option value="owners">Owners Only</option>
                    <option value="affiliates">Affiliates Only</option>
                </select>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <label for="sort_order" style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="0" min="0"
                           style="width: 100%; border: 0.5px solid #e5e7eb; border-radius: 7px; padding: 8px 12px; font-size: 13px; color: #374151; transition: all .15s;"
                           onfocus="this.style.borderColor='#185FA5'; this.style.boxShadow='0 0 0 3px rgba(24,95,165,.1)';"
                           onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none';">
                </div>
                <div class="col-6">
                    <label style="font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: 6px;">Active</label>
                    <div style="display: flex; align-items: center; gap: 8px; padding-top: 8px;">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                               style="width: 16px; height: 16px; accent-color: #185FA5;">
                        <label for="is_active" style="font-size: 13px; color: #374151;">Category is active</label>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="background: #fafafa; border-top: 0.5px solid #e5e7eb; margin: 20px -20px -20px; padding: 16px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeCategoryModal()" 
                        class="ow-btn" style="background: #f3f4f6; color: #374151; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: 0.5px solid #e5e7eb; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" 
                        class="ow-btn" style="background: #185FA5; color: #fff; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; padding: 7px 15px; border-radius: 7px; border: none; cursor: pointer; transition: all .13s;"
                        onmouseover="this.style.background='#0F4A84'; this.style.transform='translateY(-1px)';" 
                        onmouseout="this.style.background='#185FA5'; this.style.transform='translateY(0)';">
                    Save Category
                </button>
            </div>
        </form>
    </div>
</div>