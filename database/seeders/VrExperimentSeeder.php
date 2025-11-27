<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\VrExperiment;
use Illuminate\Database\Seeder;

class VrExperimentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = SchoolClass::pluck('id', 'name')->toArray();
        $subjects = Subject::pluck('id', 'subject_name')->toArray();

        $experiments = [
            // ==================== FORM 1 - PHYSICS ====================
            [
                'name' => 'Archimedes Principle Experiment',
                'class' => 'Form 1',
                'subject' => 'Physics',
                'description' => "**Objective:** Verify Archimedes' Principle - when a body is immersed in a fluid, it experiences an upthrust equal to the weight of fluid displaced.\n\n**Procedure:**\n1. Weigh the object in air using a spring balance\n2. Fill the Eureka can with water until it overflows\n3. Place an empty measuring cylinder under the spout\n4. Lower the object into the water completely\n5. Collect the displaced water in the measuring cylinder\n6. Weigh the object while submerged\n7. Calculate the upthrust (weight in air - weight in water)\n8. Compare with the weight of displaced water\n\n**Expected Outcome:** The upthrust equals the weight of water displaced, verifying Archimedes' Principle.",
                'external_url' => 'https://filbertayo.edu/en/simulations/buoyancy',
                'is_active' => true,
            ],

            // ==================== FORM 2 - PHYSICS ====================
            [
                'name' => 'Static Electricity - Charging by Friction',
                'class' => 'Form 2',
                'subject' => 'Physics',
                'description' => "**Objective:** Understand how static charges are produced when two materials are rubbed together.\n\n**Procedure:**\n1. Rub a plastic pen/ebonite rod with fur or cloth\n2. Bring the rubbed rod near small pieces of paper\n3. Observe the attraction between the rod and paper\n4. Repeat with glass rod rubbed with silk\n5. Test attraction and repulsion between different charged rods\n\n**Expected Outcome:** Students observe that friction transfers electrons between materials, creating positive and negative charges that attract neutral objects and repel like charges.",
                'external_url' => 'https://filbertayo.edu/en/simulations/balloons-and-static-electricity',
                'is_active' => true,
            ],
            [
                'name' => 'Series and Parallel Circuits',
                'class' => 'Form 2',
                'subject' => 'Physics',
                'description' => "**Objective:** Build and compare series and parallel circuits, measuring current and voltage.\n\n**Procedure:**\n1. Connect two bulbs in SERIES with a battery and switch\n2. Measure current at different points using ammeter\n3. Measure voltage across each bulb using voltmeter\n4. Now connect the same bulbs in PARALLEL\n5. Measure current in main circuit and each branch\n6. Measure voltage across each bulb\n7. Compare brightness of bulbs in both configurations\n\n**Expected Outcome:** In series: same current throughout, voltage divides. In parallel: same voltage, current divides. Parallel bulbs shine brighter.",
                'external_url' => 'https://filbertayo.edu/en/simulations/circuit-construction-kit-dc',
                'is_active' => true,
            ],
            [
                'name' => 'Magnetic Field Lines',
                'class' => 'Form 2',
                'subject' => 'Physics',
                'description' => "**Objective:** Map the magnetic field pattern around a bar magnet using iron filings or compass.\n\n**Procedure:**\n1. Place a bar magnet on a flat surface\n2. Cover with a sheet of white paper\n3. Sprinkle iron filings evenly over the paper\n4. Gently tap the paper to allow filings to align\n5. Observe and sketch the pattern formed\n6. Alternatively, use a small compass to trace field lines\n7. Note the direction from North to South pole\n\n**Expected Outcome:** Iron filings reveal curved lines running from North to South pole, showing the magnetic field is strongest at the poles.",
                'external_url' => 'https://filbertayo.edu/en/simulations/magnets-and-electromagnets',
                'is_active' => true,
            ],
            [
                'name' => 'Principle of Moments',
                'class' => 'Form 2',
                'subject' => 'Physics',
                'description' => "**Objective:** Verify the principle of moments - for a balanced beam, clockwise moments equal anticlockwise moments.\n\n**Procedure:**\n1. Balance a meter rule on a knife edge at the 50cm mark\n2. Hang a known mass (M1) at distance d1 from pivot\n3. Hang another mass (M2) on opposite side\n4. Adjust position d2 until the rule balances\n5. Record M1, d1, M2, d2\n6. Calculate moments: M1 × d1 and M2 × d2\n7. Repeat with different masses\n\n**Expected Outcome:** Clockwise moment (M1 × d1) = Anticlockwise moment (M2 × d2), verifying the principle of moments.",
                'external_url' => 'https://filbertayo.edu/en/simulations/balancing-act',
                'is_active' => true,
            ],

            // ==================== FORM 3 - PHYSICS ====================
            [
                'name' => 'Concave Mirror - Focal Length',
                'class' => 'Form 3',
                'subject' => 'Physics',
                'description' => "**Objective:** Determine the focal length of a concave mirror experimentally.\n\n**Procedure:**\n1. Set up the concave mirror on a stand in a dark room\n2. Place a distant object (window/candle) as light source\n3. Move a white screen until a sharp image forms\n4. Measure distance from mirror to screen (this is focal length for distant objects)\n5. For accurate measurement, use the no-parallax method with optical pins\n6. Record multiple readings and calculate average\n\n**Expected Outcome:** Students determine the focal length and understand that f = R/2 where R is radius of curvature.",
                'external_url' => 'https://filbertayo.edu/en/simulations/geometric-optics',
                'is_active' => true,
            ],
            [
                'name' => 'Refraction and Refractive Index',
                'class' => 'Form 3',
                'subject' => 'Physics',
                'description' => "**Objective:** Determine the refractive index of glass using Snell's Law.\n\n**Procedure:**\n1. Place a glass block on white paper\n2. Draw the outline of the block\n3. Direct a ray of light at an angle to the glass surface\n4. Mark the incident ray, refracted ray, and emergent ray\n5. Draw normal lines at entry and exit points\n6. Measure angles of incidence (i) and refraction (r)\n7. Calculate refractive index: n = sin(i)/sin(r)\n8. Repeat for different angles\n\n**Expected Outcome:** Refractive index of glass ≈ 1.5, demonstrating that light bends toward normal when entering denser medium.",
                'external_url' => 'https://filbertayo.edu/en/simulations/bending-light',
                'is_active' => true,
            ],
            [
                'name' => 'Law of Flotation',
                'class' => 'Form 3',
                'subject' => 'Physics',
                'description' => "**Objective:** Verify that a floating body displaces its own weight of fluid.\n\n**Procedure:**\n1. Take a test tube and add lead shots until it floats vertically\n2. Mark the water level on the test tube\n3. Weigh the loaded test tube\n4. Measure the volume of water displaced (using displacement method)\n5. Calculate weight of water displaced\n6. Compare with weight of floating test tube\n\n**Expected Outcome:** Weight of floating object = Weight of water displaced, verifying the law of flotation.",
                'external_url' => 'https://filbertayo.edu/en/simulations/density',
                'is_active' => true,
            ],
            [
                'name' => 'Thermal Expansion of Solids',
                'class' => 'Form 3',
                'subject' => 'Physics',
                'description' => "**Objective:** Demonstrate that solids expand when heated using ball and ring apparatus.\n\n**Procedure:**\n1. At room temperature, pass the ball through the ring - it should fit\n2. Heat the ball using a Bunsen burner for 2-3 minutes\n3. Try to pass the heated ball through the ring\n4. Allow the ball to cool and try again\n5. Alternatively, heat the ring instead\n6. Observe the results\n\n**Expected Outcome:** The heated ball cannot pass through the cold ring due to expansion. After cooling, it passes through again. This demonstrates thermal expansion of solids.",
                'external_url' => 'https://filbertayo.edu/en/simulations/states-of-matter',
                'is_active' => true,
            ],
            [
                'name' => "Charles's Law - Gas Volume and Temperature",
                'class' => 'Form 3',
                'subject' => 'Physics',
                'description' => "**Objective:** Investigate the relationship between volume and temperature of a gas at constant pressure.\n\n**Procedure:**\n1. Set up a capillary tube with trapped air column (sealed by mercury)\n2. Place in a water bath with thermometer\n3. Record initial temperature and air column length\n4. Heat the water gradually\n5. Record temperature and column length at intervals\n6. Plot graph of Volume (length) vs Temperature (K)\n7. Extrapolate to find absolute zero\n\n**Expected Outcome:** Volume is directly proportional to absolute temperature at constant pressure (V/T = constant). Graph is a straight line.",
                'external_url' => 'https://filbertayo.edu/en/simulations/gas-properties',
                'is_active' => true,
            ],

            // ==================== FORM 4 - PHYSICS ====================
            [
                'name' => 'Electromagnetic Induction',
                'class' => 'Form 4',
                'subject' => 'Physics',
                'description' => "**Objective:** Demonstrate how a changing magnetic field induces an electric current.\n\n**Procedure:**\n1. Connect a coil of wire to a galvanometer\n2. Move a bar magnet into the coil - observe galvanometer\n3. Hold the magnet stationary inside - observe reading\n4. Move the magnet out of the coil - observe deflection direction\n5. Repeat with faster motion - note the deflection magnitude\n6. Try with more turns in the coil\n7. Reverse magnet direction\n\n**Expected Outcome:** Moving magnet induces current (galvanometer deflects). Faster motion = larger current. Direction reverses when motion reverses. This is Faraday's Law of electromagnetic induction.",
                'external_url' => 'https://filbertayo.edu/en/simulations/faradays-law',
                'is_active' => true,
            ],
            [
                'name' => 'Half-Wave and Full-Wave Rectifier',
                'class' => 'Form 4',
                'subject' => 'Physics',
                'description' => "**Objective:** Construct rectifier circuits to convert AC to DC.\n\n**Procedure:**\n1. HALF-WAVE: Connect diode in series with AC source and load\n2. Observe output on oscilloscope - only positive half cycles pass\n3. Add smoothing capacitor and observe effect\n4. FULL-WAVE: Connect 4 diodes in bridge configuration\n5. Connect AC input and DC output to oscilloscope\n6. Observe both half cycles now contribute to output\n7. Add smoothing capacitor for steady DC\n\n**Expected Outcome:** Half-wave uses one half of AC cycle. Full-wave bridge uses both halves, providing smoother DC output. Capacitor reduces ripple.",
                'external_url' => 'https://filbertayo.edu/en/simulations/circuit-construction-kit-ac',
                'is_active' => true,
            ],
            [
                'name' => 'Wave Behavior in Ripple Tank',
                'class' => 'Form 4',
                'subject' => 'Physics',
                'description' => "**Objective:** Demonstrate reflection, refraction, diffraction, and interference of waves.\n\n**Procedure:**\n1. Set up ripple tank with light source below\n2. Generate plane waves using a straight vibrator\n3. REFLECTION: Place a barrier at an angle, observe reflected waves\n4. REFRACTION: Use a shallow region (glass plate), observe wavelength change\n5. DIFFRACTION: Create a gap in barrier, observe waves spreading\n6. INTERFERENCE: Use two point sources, observe pattern of nodes and antinodes\n\n**Expected Outcome:** Students visualize wave properties: angle of incidence = angle of reflection; waves slow and bend in shallow water; waves spread through gaps; two sources create interference pattern.",
                'external_url' => 'https://filbertayo.edu/en/simulations/wave-interference',
                'is_active' => true,
            ],
            [
                'name' => 'Simple Pendulum - Measuring g',
                'class' => 'Form 4',
                'subject' => 'Physics',
                'description' => "**Objective:** Determine acceleration due to gravity using a simple pendulum.\n\n**Procedure:**\n1. Set up pendulum with measured length L (from pivot to center of bob)\n2. Displace bob by small angle (<10°) and release\n3. Time 20 complete oscillations, calculate period T\n4. Repeat for 5 different lengths\n5. Plot graph of T² against L\n6. Calculate slope = 4π²/g\n7. Determine g from the slope\n\n**Expected Outcome:** T = 2π√(L/g), so T² = (4π²/g)L. Graph is straight line through origin. Slope gives g ≈ 9.8 m/s².",
                'external_url' => 'https://filbertayo.edu/en/simulations/pendulum-lab',
                'is_active' => true,
            ],

            // ==================== FORM 2 - CHEMISTRY ====================
            [
                'name' => 'Preparation and Properties of Oxygen',
                'class' => 'Form 2',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Prepare oxygen gas in the laboratory and demonstrate its properties.\n\n**Procedure:**\n1. Set up apparatus with hydrogen peroxide and manganese dioxide catalyst\n2. Collect oxygen gas by downward displacement of water\n3. Test with glowing splint - it should relight\n4. Burn magnesium ribbon in oxygen - observe bright white flame\n5. Burn sulfur in oxygen - observe blue flame, test product with litmus\n6. Burn phosphorus in oxygen - observe white fumes\n7. Record all observations\n\n**Expected Outcome:** Oxygen relights glowing splint. Metals burn brightly forming metal oxides. Non-metals form acidic oxides (turn blue litmus red). Oxygen supports combustion.",
                'external_url' => 'https://filbertayo.edu/en/simulations/reactants-products-and-leftovers',
                'is_active' => true,
            ],
            [
                'name' => 'Preparation and Properties of Hydrogen',
                'class' => 'Form 2',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Prepare hydrogen gas and demonstrate the 'pop' sound test.\n\n**Procedure:**\n1. Place zinc granules in a conical flask\n2. Add dilute hydrochloric acid through thistle funnel\n3. Collect hydrogen gas by downward displacement of water\n4. Test with burning splint - listen for 'pop' sound\n5. Hydrogen Reduction: Pass hydrogen over heated copper oxide\n6. Observe colour change from black CuO to brown copper\n7. Test liquid formed with cobalt chloride paper (turns pink = water)\n\n**Expected Outcome:** Hydrogen burns with 'pop' sound. Hydrogen reduces metal oxides: CuO + H₂ → Cu + H₂O. Water is formed as product.",
                'external_url' => 'https://filbertayo.edu/en/simulations/reactants-products-and-leftovers',
                'is_active' => true,
            ],
            [
                'name' => 'Properties of Water - Boiling and Melting Points',
                'class' => 'Form 2',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Investigate the physical properties of water including boiling and melting points.\n\n**Procedure:**\n1. Place ice in a beaker with thermometer\n2. Heat gently and record temperature as ice melts\n3. Continue heating and record boiling point\n4. Test water with anhydrous copper(II) sulphate (turns blue)\n5. Test with cobalt chloride paper (turns pink)\n6. Add small pieces of sodium/potassium to water - observe reaction\n7. Test resulting solution with litmus paper\n\n**Expected Outcome:** Ice melts at 0°C, water boils at 100°C. Anhydrous CuSO₄ and cobalt chloride detect water. Reactive metals produce hydrogen and alkaline solutions.",
                'external_url' => 'https://filbertayo.edu/en/simulations/states-of-matter',
                'is_active' => true,
            ],
            [
                'name' => 'Energy Conversion Experiment',
                'class' => 'Form 2',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Demonstrate conversion of chemical energy to electrical and light energy.\n\n**Procedure:**\n1. Set up a simple electrochemical cell using copper and zinc strips\n2. Place strips in dilute sulphuric acid\n3. Connect strips to a small lamp bulb with wires\n4. Observe the bulb lighting up\n5. Alternative: Rub magnesium ribbon with abrasive paper\n6. Add to copper sulphate solution\n7. Observe temperature change (exothermic reaction)\n\n**Expected Outcome:** Chemical energy converts to electrical energy (cell powers bulb). Displacement reaction releases heat (chemical to thermal energy).",
                'external_url' => 'https://filbertayo.edu/en/simulations/battery-voltage',
                'is_active' => true,
            ],
            [
                'name' => 'Acids and Bases - pH Testing',
                'class' => 'Form 2',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Identify acids and bases using indicators and pH scale.\n\n**Procedure:**\n1. Prepare solutions of common acids (vinegar, lemon juice) and bases (soap, baking soda)\n2. Add universal indicator to each solution\n3. Compare colors to pH chart\n4. Test with litmus paper (red and blue)\n5. Record observations\n6. Arrange substances from most acidic to most basic\n\n**Expected Outcome:** Acids turn blue litmus red (pH < 7), bases turn red litmus blue (pH > 7). Universal indicator shows color spectrum from red (acidic) to purple (basic).",
                'external_url' => 'https://filbertayo.edu/en/simulations/ph-scale',
                'is_active' => true,
            ],

            // ==================== FORM 3 - CHEMISTRY ====================
            [
                'name' => 'Hard Water vs Soft Water',
                'class' => 'Form 3',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Distinguish between hard water and soft water using soap solution.\n\n**Procedure:**\n1. Prepare samples: distilled water (soft), tap water, water with CaCl₂, water with MgSO₄\n2. Add equal amounts of soap solution to each sample\n3. Shake vigorously and observe lather formation\n4. Soft water produces lather easily; hard water forms scum\n5. Boil the hard water samples and repeat test\n6. Compare results - which hardness was removed by boiling?\n\n**Expected Outcome:** Soft water lathers easily. Temporary hardness (Ca(HCO₃)₂) removed by boiling. Permanent hardness (CaSO₄, MgSO₄) not removed by boiling.",
                'external_url' => 'https://filbertayo.edu/en/simulations/concentration',
                'is_active' => true,
            ],
            [
                'name' => 'Reactions of Acids with Metals and Carbonates',
                'class' => 'Form 3',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Investigate reactions of acids with metals, metal oxides, carbonates, and hydroxides.\n\n**Procedure:**\n1. Add dilute HCl to zinc granules - collect gas and test (pop sound = H₂)\n2. Add dilute HCl to magnesium ribbon - observe vigorous reaction\n3. Add dilute HCl to CaCO₃ - collect gas and pass through limewater (turns milky = CO₂)\n4. Add dilute H₂SO₄ to CuO (black) - observe blue solution forming\n5. Add dilute HCl to NaOH solution with indicator - observe colour change at neutralization\n\n**Expected Outcome:** Acid + Metal → Salt + Hydrogen. Acid + Carbonate → Salt + Water + CO₂. Acid + Base → Salt + Water (neutralization).",
                'external_url' => 'https://filbertayo.edu/en/simulations/acid-base-solutions',
                'is_active' => true,
            ],
            [
                'name' => 'Volumetric Titration',
                'class' => 'Form 3',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Perform acid-base titration to determine unknown concentration.\n\n**Procedure:**\n1. Rinse and fill burette with sodium hydroxide solution\n2. Pipette exactly 25.0 ml of dilute HCl into conical flask\n3. Add 2-3 drops of phenolphthalein indicator\n4. Record initial burette reading\n5. Add NaOH slowly while swirling, until pink colour persists\n6. Record final burette reading\n7. Repeat until concordant results (within 0.1 ml)\n8. Calculate using: n₁V₁/a = n₂V₂/b\n\n**Expected Outcome:** Students master burette and pipette technique. Calculate unknown concentration from titre values. Understand stoichiometry in neutralization.",
                'external_url' => 'https://filbertayo.edu/en/simulations/molarity',
                'is_active' => true,
            ],
            [
                'name' => 'Electrolysis of Different Electrolytes',
                'class' => 'Form 3',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Identify products of electrolysis for different electrolytes.\n\n**Procedure:**\n1. Electrolysis of dilute H₂SO₄: Collect gases at both electrodes, test with splints\n2. Electrolysis of NaCl solution: Test gas at anode (bleaches litmus = Cl₂)\n3. Electrolysis of CuSO₄ with copper electrodes: Observe copper depositing\n4. Electrolysis of CuSO₄ with carbon electrodes: Observe copper at cathode, oxygen at anode\n5. Record all observations\n\n**Expected Outcome:** At cathode: H₂ or metal deposited. At anode: O₂, Cl₂, or metal dissolves. Products depend on electrolyte and electrode material.",
                'external_url' => 'https://filbertayo.edu/en/simulations/molecule-polarity',
                'is_active' => true,
            ],
            [
                'name' => 'Electroplating Metals',
                'class' => 'Form 3',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Electroplate a metal object with copper.\n\n**Procedure:**\n1. Clean a metal spoon (or iron nail) thoroughly with sandpaper\n2. Set up electrolytic cell with copper sulphate solution\n3. Use pure copper strip as anode (+)\n4. Use the spoon/nail as cathode (-)\n5. Connect to low voltage DC supply (2-4V)\n6. Leave for 15-20 minutes\n7. Remove and observe copper coating on spoon\n\n**Expected Outcome:** Copper ions (Cu²⁺) gain electrons at cathode and deposit as copper metal. Anode dissolves to replace Cu²⁺ ions. Object becomes copper-plated.",
                'external_url' => 'https://filbertayo.edu/en/simulations/battery-voltage',
                'is_active' => true,
            ],
            [
                'name' => 'Rate of Chemical Reactions',
                'class' => 'Form 3',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Investigate factors affecting the rate of chemical reactions.\n\n**Procedure:**\n1. React zinc granules with dilute HCl - time until reaction stops\n2. Repeat with zinc powder - compare rates\n3. React marble chips with dilute HCl at room temperature\n4. Repeat with heated acid - compare rates\n5. React same mass of marble with different concentrations of acid\n6. Plot graphs of volume of gas vs time\n7. Compare gradients (steeper = faster rate)\n\n**Expected Outcome:** Rate increases with: smaller particle size, higher temperature, higher concentration. Gradient of gas volume vs time graph shows rate.",
                'external_url' => 'https://filbertayo.edu/en/simulations/reactions-and-rates',
                'is_active' => true,
            ],

            // ==================== FORM 4 - CHEMISTRY ====================
            [
                'name' => 'Qualitative Analysis - Preliminary Tests',
                'class' => 'Form 4',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Perform preliminary tests to identify unknown salts.\n\n**Procedure:**\n1. APPEARANCE: Note colour and physical state of sample\n2. ACTION OF HEAT: Heat sample in test tube, observe gases/residue\n3. FLAME TEST: Dip nichrome wire in sample, hold in flame - note colour\n4. SOLUBILITY: Test if sample dissolves in water, then dilute acids\n5. Add dilute HCl to solid - effervescence indicates carbonate/hydrogen carbonate\n6. Add NaOH solution - ammonia smell indicates ammonium compound\n7. Record all observations systematically\n\n**Expected Outcome:** Identify presence of: carbonates (CO₂ with acid), ammonium (NH₃ with alkali), coloured ions (Cu²⁺ blue, Fe³⁺ brown). Flame colours: Na⁺ yellow, K⁺ lilac, Ca²⁺ brick-red.",
                'external_url' => 'https://filbertayo.edu/en/simulations/concentration',
                'is_active' => true,
            ],
            [
                'name' => 'Qualitative Analysis - Identifying Anions',
                'class' => 'Form 4',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Identify anions (negative ions) in unknown salt solutions.\n\n**Procedure:**\n1. CHLORIDE (Cl⁻): Add dilute HNO₃ then AgNO₃ - white precipitate\n2. SULPHATE (SO₄²⁻): Add dilute HCl then BaCl₂ - white precipitate\n3. CARBONATE (CO₃²⁻): Add dilute HCl - effervescence, gas turns limewater milky\n4. NITRATE (NO₃⁻): Add NaOH + Al foil, warm - ammonia released\n5. Confirm each test with known samples\n6. Apply tests to unknown sample\n7. Record results in table format\n\n**Expected Outcome:** Students can systematically identify Cl⁻, SO₄²⁻, CO₃²⁻, NO₃⁻ using specific reagents and characteristic reactions.",
                'external_url' => 'https://filbertayo.edu/en/simulations/acid-base-solutions',
                'is_active' => true,
            ],
            [
                'name' => 'Qualitative Analysis - Identifying Cations',
                'class' => 'Form 4',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Identify cations (positive ions) in unknown salt solutions.\n\n**Procedure:**\n1. Add NaOH solution dropwise to sample:\n   - White ppt soluble in excess = Al³⁺ or Zn²⁺\n   - White ppt insoluble = Ca²⁺, Mg²⁺\n   - Blue ppt = Cu²⁺\n   - Green ppt turning brown = Fe²⁺\n   - Brown ppt = Fe³⁺\n2. Add NH₃ solution - some precipitates dissolve in excess\n3. Perform flame test for confirmation\n4. Test unknown sample systematically\n\n**Expected Outcome:** Identify Cu²⁺, Fe²⁺, Fe³⁺, Al³⁺, Zn²⁺, Ca²⁺, Mg²⁺ using NaOH and NH₃ tests with characteristic precipitate colours and solubility.",
                'external_url' => 'https://filbertayo.edu/en/simulations/concentration',
                'is_active' => true,
            ],
            [
                'name' => 'Preparation of Salts',
                'class' => 'Form 4',
                'subject' => 'Chemistry',
                'description' => "**Objective:** Prepare soluble and insoluble salts using different methods.\n\n**Procedure:**\n1. SOLUBLE SALT (e.g., CuSO₄): Add excess CuO to warm dilute H₂SO₄\n   - Filter to remove excess CuO\n   - Evaporate filtrate to crystallization point\n   - Cool to obtain crystals\n2. INSOLUBLE SALT (e.g., BaSO₄): Mix solutions of BaCl₂ and Na₂SO₄\n   - Filter the precipitate\n   - Wash with distilled water\n   - Dry between filter papers\n\n**Expected Outcome:** Soluble salts prepared by reaction then crystallization. Insoluble salts prepared by precipitation. Understand which method suits which salt based on solubility rules.",
                'external_url' => 'https://filbertayo.edu/en/simulations/molarity',
                'is_active' => true,
            ],

            // ==================== FORM 1 - BIOLOGY ====================
            [
                'name' => 'Classification of Living Things',
                'class' => 'Form 1',
                'subject' => 'Biology',
                'description' => "**Objective:** Understand the concept of classification and group organisms by similarities and differences.\n\n**Procedure:**\n1. Observe a variety of living organisms (pictures/specimens)\n2. List observable characteristics of each organism\n3. Group organisms with similar characteristics together\n4. Create categories based on: movement, feeding, reproduction\n5. Compare your groupings with scientific classification\n6. Learn the hierarchy: Kingdom → Phylum → Class → Order → Family → Genus → Species\n7. Practice using dichotomous keys\n\n**Expected Outcome:** Understand why classification is important for studying biodiversity. Recognize that organisms are grouped based on shared characteristics. Learn the difference between artificial and natural classification systems.",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/classification',
                'is_active' => true,
            ],
            [
                'name' => 'Microscope Use and Cell Observation',
                'class' => 'Form 1',
                'subject' => 'Biology',
                'description' => "**Objective:** Learn to use a microscope and observe plant and animal cells.\n\n**Procedure:**\n1. Place prepared slide on microscope stage\n2. Start with lowest power objective (4x)\n3. Focus using coarse adjustment knob\n4. Center specimen of interest in field of view\n5. Switch to medium power (10x), refocus with fine adjustment\n6. Switch to high power (40x), use only fine adjustment\n7. Draw and label observed cells\n8. Compare onion epidermal cells (plant) with cheek cells (animal)\n\n**Expected Outcome:** Identify cell wall, nucleus, cytoplasm, vacuole in plant cells. Animal cells lack cell wall and have smaller vacuoles. Calculate total magnification = eyepiece × objective.",
                'external_url' => 'https://www.cellsalive.com/cells/cell_model.htm',
                'is_active' => true,
            ],
            [
                'name' => 'Structure of Viruses',
                'class' => 'Form 1',
                'subject' => 'Biology',
                'description' => "**Objective:** Understand the structure and characteristics of viruses.\n\n**Procedure:**\n1. Study micrographs and diagrams of different viruses\n2. Identify common structural features: protein coat (capsid), genetic material (DNA or RNA)\n3. Compare structures of: Bacteriophage, HIV, Influenza virus, COVID-19\n4. Note that viruses lack cellular organelles\n5. Discuss why viruses are not considered living organisms\n6. Learn about viral replication cycle (lytic and lysogenic)\n7. Identify diseases caused by viruses\n\n**Expected Outcome:** Understand that viruses are non-cellular, contain either DNA or RNA, require host cells to reproduce. Know examples of viral diseases (COVID-19, HIV/AIDS, measles, polio).",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/virus-explorer',
                'is_active' => true,
            ],
            [
                'name' => 'Kingdom Monera - Bacteria Structure',
                'class' => 'Form 1',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the structure and characteristics of bacteria (Kingdom Monera).\n\n**Procedure:**\n1. Observe prepared slides/micrographs of bacteria\n2. Identify bacterial shapes: cocci (spherical), bacilli (rod), spirilla (spiral)\n3. Draw and label bacterial cell structure: cell wall, cell membrane, cytoplasm, ribosomes, nucleoid (DNA), flagella, pili\n4. Note absence of membrane-bound organelles (prokaryotic)\n5. Compare with eukaryotic cells\n6. Discuss beneficial bacteria (nitrogen fixation, yogurt, decomposition)\n7. Discuss harmful bacteria (pathogenic - cause disease)\n\n**Expected Outcome:** Understand prokaryotic cell structure. Differentiate bacteria from other organisms. Know examples of beneficial and pathogenic bacteria.",
                'external_url' => 'https://www.cellsalive.com/cells/cell_model.htm',
                'is_active' => true,
            ],
            [
                'name' => 'Kingdom Protoctista - Amoeba, Euglena, Paramecium',
                'class' => 'Form 1',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the structure and characteristics of protists.\n\n**Procedure:**\n1. Observe live/preserved specimens under microscope\n2. AMOEBA: Identify pseudopodia, food vacuole, contractile vacuole, nucleus\n3. EUGLENA: Identify flagellum, eyespot, chloroplasts, pellicle\n4. PARAMECIUM: Identify cilia, oral groove, food vacuoles, contractile vacuole, macro/micronucleus\n5. Observe movement patterns of each organism\n6. Compare feeding methods (phagocytosis, photosynthesis, filter feeding)\n7. Draw and label each organism\n\n**Expected Outcome:** Understand diversity within Protoctista. Identify structures adapted for movement, feeding, and osmoregulation. Know that Euglena can photosynthesize (plant-like) and move (animal-like).",
                'external_url' => 'https://www.cellsalive.com/cells/cell_model.htm',
                'is_active' => true,
            ],
            [
                'name' => 'Kingdom Fungi - Yeast, Mucor, Mushroom',
                'class' => 'Form 1',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the structure and characteristics of fungi.\n\n**Procedure:**\n1. YEAST: Observe under microscope - single cells, budding\n2. MUCOR (bread mold): Identify mycelium, sporangia, spores, rhizoids\n3. MUSHROOM: Identify cap, gills, stalk, mycelium underground\n4. Note common features: cell walls (chitin), no chlorophyll, heterotrophic\n5. Observe spore release from mushroom gills\n6. Discuss beneficial fungi (yeast in bread/beer, antibiotics from Penicillium)\n7. Discuss harmful fungi (athlete's foot, crop diseases)\n\n**Expected Outcome:** Understand fungal structure and reproduction by spores. Know fungi are decomposers (saprophytes). Identify useful and harmful fungi.",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/fungi',
                'is_active' => true,
            ],

            // ==================== FORM 2 - BIOLOGY ====================
            [
                'name' => 'Kingdom Plantae - Plant Divisions',
                'class' => 'Form 2',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the major divisions of Kingdom Plantae.\n\n**Procedure:**\n1. Observe specimens from different plant divisions\n2. BRYOPHYTA (Mosses): No true roots/stems/leaves, no vascular tissue\n3. FILICINOPHYTA (Ferns): True leaves (fronds), vascular tissue, reproduce by spores\n4. CONIFEROPHYTA (Conifers): Bear cones, needle-like leaves\n5. ANGIOSPERMOPHYTA (Flowering plants): Bear flowers and fruits\n6. Compare adaptations for life on land\n7. Create a comparison table of characteristics\n\n**Expected Outcome:** Understand plant evolution from simple to complex. Identify key features of each division. Recognize the importance of vascular tissue and seeds for land adaptation.",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/plant-evolution',
                'is_active' => true,
            ],
            [
                'name' => 'Structure of Mosses (Bryophyta)',
                'class' => 'Form 2',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the structure and life cycle of mosses.\n\n**Procedure:**\n1. Collect moss samples from damp areas\n2. Observe with hand lens and microscope\n3. Identify: leaf-like structures, stem-like axis, rhizoids (root-like)\n4. Find the capsule (sporophyte) on a stalk\n5. Examine spore release from capsule\n6. Note: no true vascular tissue (xylem/phloem)\n7. Understand alternation of generations (gametophyte dominant)\n\n**Expected Outcome:** Understand mosses are non-vascular plants. Know they require moist environments for reproduction (swimming sperm). Identify the dominant gametophyte generation.",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/plant-evolution',
                'is_active' => true,
            ],
            [
                'name' => 'Structure of Ferns (Filicinophyta)',
                'class' => 'Form 2',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the structure and reproduction of ferns.\n\n**Procedure:**\n1. Examine a complete fern plant\n2. Identify: fronds (leaves), rachis (leaf stalk), rhizome (underground stem), roots\n3. Look for sori (clusters of sporangia) on underside of fronds\n4. Examine young fern fronds (fiddleheads/croziers)\n5. Note the vascular tissue in cross-section\n6. Understand the life cycle - sporophyte dominant\n7. Compare with mosses\n\n**Expected Outcome:** Understand ferns have vascular tissue but no seeds. Know they reproduce by spores. Recognize the dominant sporophyte generation (the visible plant).",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/plant-evolution',
                'is_active' => true,
            ],
            [
                'name' => 'Food Nutrients and Balanced Diet',
                'class' => 'Form 2',
                'subject' => 'Biology',
                'description' => "**Objective:** Understand food nutrients and the concept of balanced diet.\n\n**Procedure:**\n1. Identify the six classes of food nutrients:\n   - Carbohydrates (energy)\n   - Proteins (growth and repair)\n   - Lipids/Fats (energy storage, insulation)\n   - Vitamins (metabolic regulation)\n   - Minerals (various functions)\n   - Water (transport, temperature regulation)\n2. List food sources for each nutrient\n3. Plan a balanced meal for different groups (children, pregnant women, manual workers)\n4. Discuss nutritional deficiency diseases\n\n**Expected Outcome:** Know functions and sources of each nutrient. Understand that balanced diet varies with age, activity, and condition. Recognize deficiency diseases (kwashiorkor, marasmus, scurvy, rickets).",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/nutrition',
                'is_active' => true,
            ],
            [
                'name' => 'Food Tests - Starch, Sugars, Proteins, Lipids',
                'class' => 'Form 2',
                'subject' => 'Biology',
                'description' => "**Objective:** Perform biochemical tests to identify food nutrients.\n\n**Procedure:**\n1. STARCH TEST: Add iodine solution → blue-black colour = starch present\n2. REDUCING SUGAR (glucose): Add Benedict's solution, heat → orange/brick-red precipitate\n3. NON-REDUCING SUGAR (sucrose): Boil with dilute HCl, neutralize with NaOH, then Benedict's test\n4. PROTEIN (Biuret test): Add dilute NaOH then CuSO₄ → purple/violet colour\n5. LIPIDS: Rub on paper → translucent mark; OR Sudan III → red colour\n6. Test various foods: egg, potato, groundnuts, bread, cooking oil\n7. Record results in a table\n\n**Expected Outcome:** Master food testing techniques. Correctly identify nutrients in unknown samples. Understand the chemistry behind each test.",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/food-tests',
                'is_active' => true,
            ],
            [
                'name' => 'Photosynthesis Investigation',
                'class' => 'Form 2',
                'subject' => 'Biology',
                'description' => "**Objective:** Investigate the requirements and products of photosynthesis.\n\n**Procedure:**\n1. TEST FOR OXYGEN: Place Elodea in water, collect gas, test with glowing splint\n2. TEST FOR STARCH: Destarch a plant (dark for 48hrs), expose half leaf to light, test with iodine\n3. NEED FOR CO₂: Use sodium hydroxide to absorb CO₂, compare with control\n4. NEED FOR CHLOROPHYLL: Use variegated leaf, test green and white parts\n5. NEED FOR LIGHT: Compare parts of leaf in light vs covered\n6. Write the photosynthesis equation: 6CO₂ + 6H₂O → C₆H₁₂O₆ + 6O₂\n\n**Expected Outcome:** Demonstrate that photosynthesis requires light, CO₂, chlorophyll, and produces oxygen and starch (glucose).",
                'external_url' => 'https://filbertayo.edu/en/simulations/greenhouse',
                'is_active' => true,
            ],

            // ==================== FORM 3 - BIOLOGY ====================
            [
                'name' => 'Human Digestive System',
                'class' => 'Form 3',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the structure and function of the human digestive system.\n\n**Procedure:**\n1. Study diagram/model of digestive system\n2. Identify organs: mouth, oesophagus, stomach, small intestine (duodenum, jejunum, ileum), large intestine, rectum, anus\n3. Identify accessory organs: salivary glands, liver, pancreas, gall bladder\n4. Trace the path of food through the alimentary canal\n5. Learn enzymes at each stage and their substrates/products\n6. Understand absorption in small intestine (villi structure)\n7. Compare with ruminant digestive system\n\n**Expected Outcome:** Know the organs and their functions. Understand mechanical and chemical digestion. Know the role of enzymes (amylase, pepsin, lipase, etc.).",
                'external_url' => 'https://www.getbodysmart.com/digestive-system',
                'is_active' => true,
            ],
            [
                'name' => 'Mammalian Heart Structure and Function',
                'class' => 'Form 3',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the structure of the mammalian heart and blood circulation.\n\n**Procedure:**\n1. Examine heart model/diagram or perform virtual dissection\n2. Identify: left and right atria, left and right ventricles\n3. Locate valves: tricuspid, bicuspid (mitral), semi-lunar valves\n4. Identify blood vessels: aorta, pulmonary artery, vena cava, pulmonary veins\n5. Compare thickness of chamber walls (left ventricle thickest)\n6. Trace pulmonary circulation (heart → lungs → heart)\n7. Trace systemic circulation (heart → body → heart)\n\n**Expected Outcome:** Understand double circulation. Know why left ventricle is thicker. Understand the role of valves in preventing backflow.",
                'external_url' => 'https://www.getbodysmart.com/heart-anatomy',
                'is_active' => true,
            ],
            [
                'name' => 'Plant Mineral Nutrition',
                'class' => 'Form 3',
                'subject' => 'Biology',
                'description' => "**Objective:** Investigate the role of mineral elements in plant nutrition.\n\n**Procedure:**\n1. Set up water culture experiments with:\n   - Complete nutrient solution (control)\n   - Solution lacking nitrogen (-N)\n   - Solution lacking phosphorus (-P)\n   - Solution lacking potassium (-K)\n   - Solution lacking magnesium (-Mg)\n2. Grow seedlings for 2-3 weeks\n3. Observe and record deficiency symptoms:\n   - -N: yellowing of older leaves, stunted growth\n   - -P: purple leaves, poor root development\n   - -K: brown leaf edges, weak stems\n   - -Mg: yellow between leaf veins (chlorosis)\n\n**Expected Outcome:** Understand roles of macro and micronutrients. Recognize deficiency symptoms. Know the importance of fertilizers in agriculture.",
                'external_url' => 'https://filbertayo.edu/en/simulations/greenhouse',
                'is_active' => true,
            ],

            // ==================== FORM 4 - BIOLOGY ====================
            [
                'name' => 'Human Reproductive System',
                'class' => 'Form 4',
                'subject' => 'Biology',
                'description' => "**Objective:** Study the structure and function of human reproductive systems.\n\n**Procedure:**\n1. MALE SYSTEM: Identify testes, epididymis, vas deferens, seminal vesicles, prostate gland, urethra, penis\n2. FEMALE SYSTEM: Identify ovaries, fallopian tubes (oviducts), uterus, cervix, vagina\n3. Understand gametogenesis (sperm and egg formation)\n4. Trace the path of sperm and egg\n5. Learn about fertilization and implantation\n6. Study the menstrual cycle (28-day cycle, hormones involved)\n7. Discuss contraception methods\n\n**Expected Outcome:** Know the organs and their functions. Understand hormonal control of reproduction. Know the stages of the menstrual cycle.",
                'external_url' => 'https://www.getbodysmart.com/reproductive-system',
                'is_active' => true,
            ],
            [
                'name' => 'Genetics and Inheritance',
                'class' => 'Form 4',
                'subject' => 'Biology',
                'description' => "**Objective:** Understand the principles of genetics and inheritance.\n\n**Procedure:**\n1. Learn key terms: gene, allele, genotype, phenotype, dominant, recessive\n2. Study Mendel's experiments with pea plants\n3. Perform monohybrid crosses using Punnett squares\n4. Calculate phenotypic and genotypic ratios (3:1, 1:2:1)\n5. Study dihybrid crosses (9:3:3:1 ratio)\n6. Understand sex determination (XX, XY chromosomes)\n7. Study sex-linked inheritance (colour blindness, haemophilia)\n\n**Expected Outcome:** Predict offspring ratios using Punnett squares. Understand dominant/recessive inheritance. Know examples of sex-linked traits.",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/genetics',
                'is_active' => true,
            ],
            [
                'name' => 'Evolution and Natural Selection',
                'class' => 'Form 4',
                'subject' => 'Biology',
                'description' => "**Objective:** Understand the theory of evolution by natural selection.\n\n**Procedure:**\n1. Study evidence for evolution:\n   - Fossil record\n   - Comparative anatomy (homologous structures)\n   - Embryology\n   - Molecular biology (DNA similarities)\n2. Understand Darwin's theory of natural selection:\n   - Variation exists within populations\n   - More offspring produced than can survive\n   - Struggle for existence\n   - Survival of the fittest\n   - Favorable traits inherited\n3. Study examples: antibiotic resistance, peppered moth, Darwin's finches\n\n**Expected Outcome:** Understand how natural selection leads to evolution. Know the evidence supporting evolution. Explain adaptation and speciation.",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/evolution',
                'is_active' => true,
            ],
            [
                'name' => 'Ecology and Ecosystems',
                'class' => 'Form 4',
                'subject' => 'Biology',
                'description' => "**Objective:** Study ecological relationships and ecosystem dynamics.\n\n**Procedure:**\n1. Define: population, community, ecosystem, biosphere\n2. Construct food chains and food webs\n3. Identify trophic levels: producers, primary/secondary/tertiary consumers, decomposers\n4. Calculate energy transfer between levels (10% rule)\n5. Draw and interpret ecological pyramids (numbers, biomass, energy)\n6. Study nutrient cycles: carbon cycle, nitrogen cycle\n7. Investigate a local ecosystem (quadrat sampling)\n\n**Expected Outcome:** Understand energy flow in ecosystems. Know the roles of organisms in food webs. Understand the importance of decomposers and nutrient cycling.",
                'external_url' => 'https://www.biointeractive.org/classroom-resources/ecology',
                'is_active' => true,
            ],

            // ==================== FORM 1 - ROBOTICS ====================
            [
                'name' => 'Build a Mini Robot Bug',
                'class' => 'Form 1',
                'subject' => 'Robotics',
                'description' => "**Objective:** Get an introduction to robotics and engineering - understand what the main fields are and why robotics is important.\n\n**Materials:**\n• 2 LEDs\n• 3 Pins\n• Watch battery\n• Glue gun\n• Wire (positive and negative)\n• Vibrator motor\n• Soldering gun and wire\n• Clothes hanger wire\n• Pliers\n\n**Procedure:**\n1. Design your bug shape using clothes hanger wire for legs\n2. Create the body using available materials\n3. Connect the vibrator motor to the watch battery\n4. Add LEDs for eyes (connect to battery)\n5. Solder all connections securely\n6. Test the vibration movement\n7. Adjust leg positions for best movement\n\n**Expected Outcome:** Students understand how vibrational motors create movement. Learn basic soldering and circuit connections. Experience teamwork and problem-solving in engineering.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Build a Robotic Hand Arm',
                'class' => 'Form 1',
                'subject' => 'Robotics',
                'description' => "**Objective:** Understand how the human hand and fingers work through building a mechanical model.\n\n**Materials:**\n• Scissors\n• Manila sheet/cardboard\n• Ruler\n• Plastic straws\n• Glue\n• Sellotape\n• Threads/strings\n• Needle\n\n**Procedure:**\n1. Trace your hand on manila sheet and cut out the shape\n2. Mark finger joints (3 on each finger, 2 on thumb)\n3. Score and fold at joint marks to create bendable sections\n4. Cut straws into small pieces and glue along fingers (these act as guides)\n5. Thread string through the straw pieces on each finger\n6. Secure strings at fingertips\n7. Pull individual strings to bend each finger\n8. Test gripping objects\n\n**Expected Outcome:** Understand how tendons control finger movement. Learn about joints and mechanical advantage. Develop creativity and fine motor skills through hands-on building.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],

            // ==================== FORM 2 - ROBOTICS ====================
            [
                'name' => 'Build a Basic Car Robot',
                'class' => 'Form 2',
                'subject' => 'Robotics',
                'description' => "**Objective:** Learn about robot components, general design principles, and calculate variables like speed and distance.\n\n**Materials:**\n• Wheels (4)\n• DC Motors with gearbox (2)\n• Batteries and battery holder\n• Arduino Uno board\n• Car body (cardboard or chassis)\n• Glue gun\n• Gear wheels\n• Ultrasonic sensor\n• Connecting wires\n• STEM kit components\n\n**Procedure:**\n1. Design the robot layout on paper first\n2. Attach motors to the chassis/body\n3. Mount wheels on motor shafts\n4. Install battery holder and connect power\n5. Wire motors to Arduino motor driver\n6. Add ultrasonic sensor at front\n7. Test forward, backward, left, right movements\n8. Calculate speed: Speed = Distance ÷ Time\n9. Measure and record distances traveled\n\n**Expected Outcome:** Understand basic robot anatomy (power, control, actuators, sensors). Calculate speed and distance. Develop critical thinking, teamwork, and project planning skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Build a Human Movement Robot',
                'class' => 'Form 2',
                'subject' => 'Robotics',
                'description' => "**Objective:** Understand how humans move with legs and replicate this with a simple walking robot.\n\n**Materials:**\n• Batteries\n• DC Motor\n• Glue gun\n• Terminal connectors\n• Switch\n• Wires\n• Scissors\n• Wooden sticks/popsicle sticks\n• Piece of cardboard box\n• Marker pen\n• Bottle caps\n\n**Procedure:**\n1. Study how human legs move when walking (alternating motion)\n2. Design a simple linkage mechanism on paper\n3. Create the body frame using cardboard\n4. Build leg mechanisms using wooden sticks\n5. Connect legs to motor using a cam or crank mechanism\n6. Wire the motor to battery through switch\n7. Test and adjust leg movements for smooth walking\n8. Improve balance and stability\n9. Document what works and what doesn't\n\n**Expected Outcome:** Understand biomechanics of human walking. Learn about linkages and mechanical conversion of rotary to linear motion. Develop innovation and iterative design skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],

            // ==================== FORM 3 - ROBOTICS ====================
            [
                'name' => 'Build a Path-Driving Robot',
                'class' => 'Form 3',
                'subject' => 'Robotics',
                'description' => "**Objective:** Understand the combination of robotics and coding - program a robot to follow a specific path.\n\n**Materials:**\n• Wheels and gearboxes\n• DC Motors (2)\n• Batteries\n• Connecting wires\n• Motor driver module (L298N or similar)\n• Arduino Uno\n• Robot body/chassis\n• Laptop with Arduino IDE\n• USB cable\n\n**Procedure:**\n1. Assemble the robot chassis with motors and wheels\n2. Connect motor driver to Arduino and motors\n3. Write basic movement code:\n```\nvoid forward() { /* motor code */ }\nvoid turnLeft() { /* motor code */ }\nvoid turnRight() { /* motor code */ }\nvoid stop() { /* motor code */ }\n```\n4. Plan a path (e.g., square, figure-8)\n5. Write code sequence for the path\n6. Upload code to Arduino\n7. Test and debug the movement\n8. Optimize timing for accurate path following\n\n**Expected Outcome:** Understand how code controls robot movement. Learn programming logic and sequencing. Develop problem-solving and debugging skills through iterative testing.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Line Following Robot',
                'class' => 'Form 3',
                'subject' => 'Robotics',
                'description' => "**Objective:** Build a robot that automatically follows a black line on white surface using sensors.\n\n**Materials:**\n• Robot chassis with motors and wheels\n• Arduino Uno\n• IR sensors (2-3)\n• Motor driver module\n• Batteries\n• Connecting wires\n• Black tape\n• White surface/paper\n• Laptop with Arduino IDE\n\n**Procedure:**\n1. Assemble basic robot platform\n2. Mount IR sensors at front, facing downward\n3. Test sensors: LOW on black, HIGH on white\n4. Write control logic:\n   - Both sensors on white → go forward\n   - Left sensor on black → turn left\n   - Right sensor on black → turn right\n   - Both on black → stop (junction)\n5. Create a track with black tape\n6. Test and tune sensor sensitivity\n7. Adjust motor speeds for smooth following\n\n**Expected Outcome:** Understand sensor-based feedback control. Learn conditional programming (if-else logic). Experience real-world autonomous robot behavior.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],

            // ==================== FORM 4 - ROBOTICS ====================
            [
                'name' => 'Build an Alarm System',
                'class' => 'Form 4',
                'subject' => 'Robotics',
                'description' => "**Objective:** Understand the importance of engineering in security applications - build a motion-detecting alarm system.\n\n**Materials:**\n• Ultrasonic sensor (HC-SR04)\n• Arduino Uno\n• Breadboard\n• Bluetooth module (HC-05)\n• Buzzer/speaker\n• LED indicators\n• Laptop with Arduino IDE\n• Connecting wires\n• Smartphone with Bluetooth terminal app\n\n**Procedure:**\n1. Connect ultrasonic sensor to Arduino (Trig and Echo pins)\n2. Wire buzzer and LED to output pins\n3. Write distance measurement code:\n```\ndistance = (duration × 0.034) / 2;\n```\n4. Set threshold distance for alarm trigger\n5. Add Bluetooth module for remote notifications\n6. Program alert system:\n   - If distance < threshold → activate buzzer + LED + send Bluetooth alert\n7. Test with different distances\n8. Optimize for false alarm prevention\n9. Add arm/disarm feature via Bluetooth\n\n**Expected Outcome:** Understand sensor integration and real-world applications. Learn about IoT (Internet of Things) concepts. Develop project planning and system optimization skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Obstacle Avoiding Robot',
                'class' => 'Form 4',
                'subject' => 'Robotics',
                'description' => "**Objective:** Build an autonomous robot that detects and avoids obstacles using ultrasonic sensors.\n\n**Materials:**\n• Robot chassis with motors and wheels\n• Arduino Uno\n• Ultrasonic sensor (HC-SR04)\n• Servo motor (for sensor rotation)\n• Motor driver module (L298N)\n• Batteries\n• Connecting wires\n• Laptop with Arduino IDE\n\n**Procedure:**\n1. Assemble robot platform with motors\n2. Mount ultrasonic sensor on servo motor at front\n3. Connect all components to Arduino\n4. Write scanning code - servo rotates sensor left, center, right\n5. Measure distances in all three directions\n6. Program decision logic:\n   - If front clear → move forward\n   - If obstacle detected → stop, scan left and right\n   - Turn toward direction with more space\n   - Continue forward\n7. Test in obstacle course\n8. Tune distances and turning angles\n\n**Expected Outcome:** Understand autonomous navigation principles. Learn servo motor control and multi-sensor integration. Develop advanced programming and logical thinking skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Bluetooth Controlled Robot',
                'class' => 'Form 4',
                'subject' => 'Robotics',
                'description' => "**Objective:** Build a robot that can be controlled remotely via smartphone using Bluetooth communication.\n\n**Materials:**\n• Robot chassis with motors and wheels\n• Arduino Uno\n• Bluetooth module (HC-05 or HC-06)\n• Motor driver module (L298N)\n• Batteries\n• Connecting wires\n• Smartphone with Bluetooth controller app\n• Laptop with Arduino IDE\n\n**Procedure:**\n1. Assemble robot with motors and motor driver\n2. Connect Bluetooth module to Arduino (TX→RX, RX→TX)\n3. Write code to receive Bluetooth commands:\n   - 'F' = Forward\n   - 'B' = Backward\n   - 'L' = Left\n   - 'R' = Right\n   - 'S' = Stop\n4. Install Bluetooth controller app on phone\n5. Pair phone with HC-05 module\n6. Map app buttons to commands\n7. Test control responsiveness\n8. Add speed control feature (PWM values)\n\n**Expected Outcome:** Understand wireless communication protocols. Learn about serial communication and Bluetooth pairing. Develop skills in creating user-controlled robotic systems.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],

            // ==================== FORM 1 - ENGINEERING ====================
            [
                'name' => 'Simple Machines - Levers and Pulleys',
                'class' => 'Form 1',
                'subject' => 'Engineering',
                'description' => "**Objective:** Understand the principles of simple machines and mechanical advantage.\n\n**Materials:**\n• Wooden plank (meter rule)\n• Fulcrum (wooden block or triangular support)\n• Various masses/weights\n• Pulleys (single and double)\n• String/rope\n• Spring balance\n• Ruler and measuring tape\n\n**Procedure:**\n1. LEVERS:\n   - Set up first-class lever (fulcrum in middle)\n   - Experiment with different load and effort positions\n   - Calculate Mechanical Advantage = Load ÷ Effort\n   - Identify lever classes (1st, 2nd, 3rd) with examples\n2. PULLEYS:\n   - Measure force needed to lift weight directly\n   - Use single fixed pulley - measure effort\n   - Use single movable pulley - compare effort\n   - Create pulley system - observe mechanical advantage\n\n**Expected Outcome:** Understand how simple machines multiply force. Calculate mechanical advantage and efficiency. Recognize simple machines in everyday tools.",
                'external_url' => 'https://filbertayo.edu/en/simulations/balancing-act',
                'is_active' => true,
            ],
            [
                'name' => 'Bridge Building Challenge',
                'class' => 'Form 1',
                'subject' => 'Engineering',
                'description' => "**Objective:** Learn basic structural engineering principles by designing and building bridges.\n\n**Materials:**\n• Popsicle sticks/wooden sticks\n• Glue (wood glue or hot glue)\n• String\n• Cardboard\n• Ruler\n• Weights for testing\n• Paper for planning\n\n**Procedure:**\n1. Study different bridge types:\n   - Beam bridge (simple span)\n   - Truss bridge (triangular supports)\n   - Arch bridge\n   - Suspension bridge\n2. Design your bridge on paper (30cm span minimum)\n3. Consider: triangles are strongest shapes\n4. Build your bridge using available materials\n5. Allow glue to dry completely\n6. Test bridge strength by adding weights gradually\n7. Record maximum load before failure\n8. Analyze failure points and redesign\n\n**Expected Outcome:** Understand structural forces (tension, compression). Learn why triangles provide strength. Develop design thinking and iterative improvement skills.",
                'external_url' => 'https://www.tinkercad.com/',
                'is_active' => true,
            ],

            // ==================== FORM 2 - ENGINEERING ====================
            [
                'name' => 'Build an Energy Glider',
                'class' => 'Form 2',
                'subject' => 'Engineering',
                'description' => "**Objective:** Understand basics of aerodynamics, flying techniques, and functions of wireless remote control.\n\n**Materials:**\n• Balsa wood sheets\n• Styrofoam\n• Marker and sandpaper\n• Threads\n• 2 ultra micro servos\n• Radio transmitter and receiver\n• Battery and battery charger\n• Propeller\n• Glue and cutting tools\n• 3D printer (optional for parts)\n\n**Procedure:**\n1. Study aerodynamic principles:\n   - Lift, Drag, Thrust, Weight (four forces of flight)\n   - Airfoil shape and how it creates lift\n2. Design glider on paper:\n   - Wing span and chord length\n   - Tail (horizontal stabilizer and vertical fin)\n   - Fuselage shape\n3. Cut wing from styrofoam with airfoil profile\n4. Build fuselage from balsa wood\n5. Attach tail surfaces with correct angles\n6. Install servos for control surfaces (ailerons, elevator)\n7. Connect receiver and battery\n8. Balance the glider (center of gravity)\n9. Test glide ratio without motor first\n10. Add propeller and test powered flight\n\n**Expected Outcome:** Understand the four forces of flight. Learn how control surfaces work. Develop skills in aerodynamic design and wireless control systems.",
                'external_url' => 'https://www.flightsimulator.com/',
                'is_active' => true,
            ],
            [
                'name' => 'Water Rocket Engineering',
                'class' => 'Form 2',
                'subject' => 'Engineering',
                'description' => "**Objective:** Understand Newton's laws of motion and rocket propulsion principles.\n\n**Materials:**\n• Plastic bottles (2-liter)\n• Cardboard for fins\n• Cork or rubber stopper with valve\n• Bicycle pump with pressure gauge\n• Launch stand\n• Nose cone (plastic or paper)\n• Water\n• Tape and glue\n• Safety goggles\n\n**Procedure:**\n1. Study Newton's Third Law: Action-Reaction\n2. Design rocket with:\n   - Stable nose cone\n   - 3-4 fins at base (equally spaced)\n   - Launch tube connection\n3. Cut and attach fins using template\n4. Create aerodynamic nose cone\n5. Fill bottle 1/3 with water\n6. Seal with cork/stopper connected to pump\n7. Pump air to desired pressure (safely)\n8. Launch and observe!\n9. Experiment with different water amounts\n10. Measure flight height and duration\n\n**Expected Outcome:** Understand rocket propulsion and Newton's Third Law. Learn about stability (center of mass vs center of pressure). Experience iterative design improvement.",
                'external_url' => 'https://filbertayo.edu/en/simulations/projectile-motion',
                'is_active' => true,
            ],

            // ==================== FORM 3 - ENGINEERING ====================
            [
                'name' => 'Build RC (Radio Controlled) Aircraft',
                'class' => 'Form 3',
                'subject' => 'Engineering',
                'description' => "**Objective:** Understand the basic concepts of aviation, aircraft design, and flight principles.\n\n**Materials:**\n• Brushless DC motors\n• Electronic Speed Controller (ESC)\n• Propeller\n• Li-Po battery\n• Servo motors (for control surfaces)\n• Coroplast or Styrofoam (for airframe)\n• Control rods and connectors\n• Wing holders/spars\n• RC Transmitter and Receiver\n• Aviation kit components\n• Scissors, glue, tape\n\n**Procedure:**\n1. Study aircraft anatomy:\n   - Fuselage, wings, tail (horizontal/vertical stabilizers)\n   - Control surfaces: ailerons, elevator, rudder\n2. Design aircraft on paper with dimensions\n3. Cut fuselage from coroplast/styrofoam\n4. Build wings with proper airfoil shape\n5. Install wing spars for strength\n6. Attach tail surfaces\n7. Mount motor at nose, connect ESC\n8. Install servos and link to control surfaces\n9. Set up receiver and bind to transmitter\n10. Balance aircraft (CG at 25-30% wing chord)\n11. Perform ground tests before flight\n12. Test fly in open area\n\n**Expected Outcome:** Understand aircraft design principles. Learn about lift, drag, thrust, and weight. Master RC system integration. Develop creativity, innovation, and problem-solving skills.",
                'external_url' => 'https://www.flightsimulator.com/',
                'is_active' => true,
            ],
            [
                'name' => 'Flight Simulation Training',
                'class' => 'Form 3',
                'subject' => 'Engineering',
                'description' => "**Objective:** Experience virtual aircraft piloting and understand flight instruments and controls.\n\n**System Requirements:**\n• Computer with dedicated video RAM (2GB+)\n• Graphics card with Pixel Shader 5.0, Vertex Shader 5.0\n• Windows 10 (64-bit)\n• 150 GB free disk space\n• Flight simulator software\n• Joystick/flight controls (optional but recommended)\n\n**Procedure:**\n1. Install flight simulator software\n2. Learn the cockpit instruments:\n   - Altimeter (altitude)\n   - Airspeed indicator\n   - Attitude indicator (artificial horizon)\n   - Heading indicator (compass)\n   - Vertical speed indicator\n   - Turn coordinator\n3. Practice basic maneuvers:\n   - Takeoff procedure\n   - Level flight and turns\n   - Climbing and descending\n   - Landing approach\n4. Learn about:\n   - Throttle control\n   - Pitch, roll, and yaw\n   - Flaps and landing gear\n5. Complete training missions\n6. Try different aircraft types\n7. Practice emergency procedures\n\n**Expected Outcome:** Understand flight instruments and their functions. Learn aircraft control principles. Experience realistic flight scenarios. Develop critical thinking and decision-making skills.",
                'external_url' => 'https://www.flightsimulator.com/',
                'is_active' => true,
            ],

            // ==================== FORM 4 - ENGINEERING ====================
            [
                'name' => 'Build a Drone with Arduino and GPS',
                'class' => 'Form 4',
                'subject' => 'Engineering',
                'description' => "**Objective:** Understand the concepts of flight, coding, and GPS navigation by building a quadcopter drone.\n\n**Materials:**\n• 4 Brushless motors\n• Quadcopter frame (X or H configuration)\n• 4 Electronic Speed Controllers (20A UBEC ESC)\n• Power distribution board\n• Li-Po battery (3S or 4S)\n• Propellers (2 CW, 2 CCW)\n• Arduino Mega\n• Arduino Uno (for ground station)\n• Arduino Ultimate GPS Shield\n• HC-12 wireless transceivers (2)\n• MPU6050 Accelerometer/Gyroscope\n• Transmitter/Receiver\n• Frame stackable headers\n• USB cable, soldering equipment\n• FPV camera and video transmitter (optional)\n\n**Procedure:**\n1. Assemble quadcopter frame\n2. Mount motors at each arm (ensure correct rotation direction)\n3. Connect ESCs to power distribution board\n4. Wire ESCs to Arduino Mega PWM pins\n5. Install MPU6050 for stabilization\n6. Program basic flight controller:\n   - Read accelerometer/gyroscope data\n   - PID control for stability\n   - Motor mixing for quad configuration\n7. Install GPS shield on second Arduino\n8. Set up HC-12 communication between drone and ground\n9. Program waypoint navigation\n10. Calibrate ESCs and sensors\n11. Test in controlled environment\n12. Add FPV for first-person view flying\n\n**Expected Outcome:** Understand multirotor flight dynamics. Learn PID control and sensor integration. Master GPS navigation programming. Develop advanced computing, critical thinking, and innovation skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Building a Model Satellite (CanSat)',
                'class' => 'Form 4',
                'subject' => 'Engineering',
                'description' => "**Objective:** Understand principles of aerospace engineering and apply knowledge to design and build a miniature satellite.\n\n**Materials:**\n• Soda can (housing)\n• Arduino Nano or Micro\n• Sensors: temperature, pressure (BMP280), humidity, GPS\n• Radio transmitter module (433MHz or LoRa)\n• Battery (Li-Po or lithium)\n• Parachute material (plastic bag or cloth)\n• String\n• 3D printed parts (optional)\n• Graphite/carbon fiber (for structure)\n• Epoxy and fiberglass\n• Soldering equipment\n\n**Procedure:**\n1. Study satellite subsystems:\n   - Structure (mechanical support)\n   - Power (battery, solar cells)\n   - Communication (radio transmission)\n   - Payload (sensors, cameras)\n   - Recovery (parachute)\n2. Design your CanSat to fit in a soda can\n3. Create circuit board layout\n4. Program Arduino to:\n   - Read sensor data\n   - Transmit data via radio\n   - Log data to SD card (backup)\n5. Build structure to protect electronics\n6. Design and test parachute deployment\n7. Create ground station to receive data\n8. Test by dropping from height\n9. Analyze collected atmospheric data\n10. Present findings\n\n**Expected Outcome:** Understand satellite design principles. Learn about telemetry and data transmission. Experience end-to-end aerospace project development. Develop critical thinking, teamwork, and collaboration skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Solar-Powered Vehicle Design',
                'class' => 'Form 4',
                'subject' => 'Engineering',
                'description' => "**Objective:** Understand renewable energy engineering and design a vehicle powered by solar energy.\n\n**Materials:**\n• Solar panels (small, 5-6V)\n• DC motors\n• Wheels and axles\n• Chassis material (balsa wood, cardboard, or 3D printed)\n• Wires and connectors\n• Capacitor (for energy storage)\n• Multimeter\n• Gears (optional for torque)\n• Lightweight materials\n\n**Procedure:**\n1. Study solar cell principles:\n   - Photovoltaic effect\n   - Voltage and current output\n   - Series vs parallel connections\n2. Design vehicle for efficiency:\n   - Minimize weight\n   - Maximize solar panel area\n   - Low friction wheels and bearings\n3. Build chassis from lightweight materials\n4. Mount solar panel at optimal angle\n5. Connect motor directly or through capacitor\n6. Test voltage output in different light conditions\n7. Optimize gear ratio for speed vs torque\n8. Race against other designs\n9. Calculate energy efficiency\n10. Document design improvements\n\n**Expected Outcome:** Understand solar energy conversion. Learn about electrical circuits and energy efficiency. Apply engineering design process. Develop innovation and sustainable engineering thinking.",
                'external_url' => 'https://filbertayo.edu/en/simulations/solar-system',
                'is_active' => true,
            ],

            // ==================== FORM 1 - CODING ====================
            [
                'name' => 'Build Traffic Lights Project',
                'class' => 'Form 1',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn how traffic lights work and program them to change interchangeably using Arduino.\n\n**Materials:**\n• Computer with Arduino IDE\n• Arduino Uno board\n• LEDs (Red, Yellow, Green) - 3 each for two directions\n• Resistors (220Ω)\n• Breadboard\n• Connecting wires (jumper wires)\n• USB cable\n\n**Procedure:**\n1. Understand traffic light sequence:\n   - Green → Yellow → Red (one direction)\n   - Opposite direction follows inverse pattern\n2. Set up circuit on breadboard:\n   - Connect LEDs with resistors to Arduino digital pins\n   - Common ground connection\n3. Write Arduino code:\n```cpp\nvoid setup() {\n  pinMode(2, OUTPUT); // Red 1\n  pinMode(3, OUTPUT); // Yellow 1\n  pinMode(4, OUTPUT); // Green 1\n  // ... repeat for direction 2\n}\nvoid loop() {\n  // Direction 1: Green, Direction 2: Red\n  digitalWrite(4, HIGH); digitalWrite(7, HIGH);\n  delay(5000);\n  // Yellow transition\n  // ... continue sequence\n}\n```\n4. Upload code and test\n5. Adjust timing for realistic operation\n6. Add pedestrian crossing button (optional)\n\n**Expected Outcome:** Understand sequential logic and timing in programs. Learn basic Arduino programming. Master LED circuit connections. Develop problem-solving skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Water Level Sensor Project',
                'class' => 'Form 1',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn to manage water levels and build a simple monitoring circuit with display.\n\n**Materials:**\n• Computer with Arduino IDE\n• Arduino Uno board\n• Water level sensor\n• LCD character display (16x2)\n• LEDs (Red, Yellow, Green)\n• Breadboard and jumper wires\n• Resistors\n• Buzzer (optional for alarm)\n\n**Procedure:**\n1. Understand water level sensor:\n   - Outputs analog value based on water depth\n   - Higher water = higher voltage\n2. Connect circuit:\n   - Water sensor to analog pin A0\n   - LCD to digital pins (or I2C)\n   - LEDs to digital pins\n3. Write Arduino code:\n```cpp\nint sensorPin = A0;\nint waterLevel;\n\nvoid loop() {\n  waterLevel = analogRead(sensorPin);\n  \n  if(waterLevel < 300) {\n    // Low water - Red LED\n    lcd.print(\"LOW WATER!\");\n  } else if(waterLevel < 600) {\n    // Medium - Yellow LED\n    lcd.print(\"Medium Level\");\n  } else {\n    // Full - Green LED\n    lcd.print(\"Tank Full\");\n  }\n}\n```\n4. Calibrate sensor readings\n5. Test with actual water\n6. Add buzzer alarm for low level\n\n**Expected Outcome:** Learn analog sensor reading. Understand conditional statements (if-else). Display data on LCD. Real-world application of programming.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],

            // ==================== FORM 2 - CODING ====================
            [
                'name' => 'Build a Game with Scratch',
                'class' => 'Form 2',
                'subject' => 'Coding',
                'description' => "**Objective:** Design and build an interactive game while learning programming principles and software development approaches.\n\n**Materials:**\n• Computer with internet access\n• Scratch (scratch.mit.edu) or similar platform\n• Planning paper for game design\n\n**Procedure:**\n1. Plan your game:\n   - Game type (platformer, maze, quiz, shooter)\n   - Characters (sprites) needed\n   - Rules and scoring system\n   - Win/lose conditions\n2. Create project in Scratch:\n   - Design or choose sprites\n   - Create backdrop/background\n3. Program game mechanics:\n   - Player movement (arrow keys/WASD)\n   - Collision detection\n   - Score tracking\n   - Lives system\n4. Add game features:\n   - Start screen\n   - Instructions\n   - Game over screen\n   - Sound effects\n5. Test and debug:\n   - Play your game\n   - Fix bugs\n   - Get feedback from others\n6. Publish and share\n\n**Code Concepts Learned:**\n- Events (when key pressed)\n- Loops (forever, repeat)\n- Conditionals (if-then-else)\n- Variables (score, lives)\n- Broadcasting messages\n\n**Expected Outcome:** Understand game design principles. Learn visual programming concepts. Develop creativity, critical thinking, and problem-solving skills.",
                'external_url' => 'https://scratch.mit.edu/',
                'is_active' => true,
            ],
            [
                'name' => 'Develop a Website',
                'class' => 'Form 2',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn web programming languages (HTML, CSS) and create a personal website.\n\n**Materials:**\n• Computer with internet access\n• Text editor (VS Code, Notepad++, or online editor)\n• Web browser for testing\n• Website hosting (GitHub Pages - free)\n\n**Procedure:**\n1. Learn HTML basics:\n   - Structure: <html>, <head>, <body>\n   - Headings: <h1> to <h6>\n   - Paragraphs: <p>\n   - Links: <a href=\"...\">\n   - Images: <img src=\"...\">\n   - Lists: <ul>, <ol>, <li>\n2. Create your first page:\n```html\n<!DOCTYPE html>\n<html>\n<head>\n  <title>My Website</title>\n</head>\n<body>\n  <h1>Welcome!</h1>\n  <p>This is my first website.</p>\n</body>\n</html>\n```\n3. Learn CSS styling:\n   - Colors, fonts, sizes\n   - Margins and padding\n   - Layout (flexbox basics)\n4. Style your website:\n```css\nbody {\n  font-family: Arial;\n  background-color: #f0f0f0;\n}\nh1 {\n  color: blue;\n}\n```\n5. Add multiple pages and navigation\n6. Make it responsive (mobile-friendly)\n7. Publish online\n\n**Expected Outcome:** Understand web page structure. Learn HTML and CSS syntax. Create and publish a real website. Develop design and communication skills.",
                'external_url' => 'https://www.w3schools.com/',
                'is_active' => true,
            ],

            // ==================== FORM 3 - CODING ====================
            [
                'name' => 'Build a Weather App',
                'class' => 'Form 3',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn how to use APIs to access weather data and create data visualizations.\n\n**Materials:**\n• Computer with internet access\n• Code editor (VS Code or online IDE)\n• Weather API key (OpenWeatherMap - free tier)\n• Web browser\n\n**Procedure:**\n1. Sign up for OpenWeatherMap API (free)\n2. Understand APIs:\n   - What is an API? (Application Programming Interface)\n   - How to make API requests\n   - JSON data format\n3. Create HTML structure:\n```html\n<input type=\"text\" id=\"city\" placeholder=\"Enter city\">\n<button onclick=\"getWeather()\">Get Weather</button>\n<div id=\"result\"></div>\n```\n4. Write JavaScript to fetch data:\n```javascript\nasync function getWeather() {\n  const city = document.getElementById('city').value;\n  const apiKey = 'YOUR_API_KEY';\n  const url = 'https://api.openweathermap.org/data/2.5/weather?q=' + city + '&appid=' + apiKey;\n  \n  const response = await fetch(url);\n  const data = await response.json();\n  \n  displayWeather(data);\n}\n```\n5. Display weather information:\n   - Temperature\n   - Humidity\n   - Weather description\n   - Weather icon\n6. Add styling with CSS\n7. Handle errors (city not found)\n8. Add 5-day forecast feature\n\n**Expected Outcome:** Understand how APIs work. Learn asynchronous JavaScript. Parse and display JSON data. Develop skills in data visualization and communication.",
                'external_url' => 'https://openweathermap.org/',
                'is_active' => true,
            ],
            [
                'name' => 'Create a Mobile App',
                'class' => 'Form 3',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn mobile app development techniques using MIT App Inventor or Thunkable.\n\n**Materials:**\n• Computer with internet access\n• MIT App Inventor (appinventor.mit.edu) or Thunkable\n• Android phone or emulator for testing\n• App idea/plan\n\n**Procedure:**\n1. Plan your app:\n   - Purpose (calculator, to-do list, quiz, etc.)\n   - Screens needed\n   - User interface design\n   - Features list\n2. Create project in App Inventor:\n   - Design screen layout\n   - Add components (buttons, labels, text inputs)\n   - Arrange using layouts\n3. Program functionality (blocks):\n   - Button click events\n   - Store and retrieve data\n   - Navigate between screens\n4. Example - Simple Calculator:\n```\nWhen Button_Add.Click:\n  Set Label_Result.Text to\n    (TextBox_Num1.Text + TextBox_Num2.Text)\n```\n5. Test on phone:\n   - Use AI Companion app\n   - Scan QR code to connect\n6. Debug and improve\n7. Build APK file\n8. Share your app\n\n**Expected Outcome:** Understand mobile app architecture. Learn event-driven programming. Design user interfaces. Develop software engineering thinking.",
                'external_url' => 'https://appinventor.mit.edu/',
                'is_active' => true,
            ],
            [
                'name' => 'Program a Robot',
                'class' => 'Form 3',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn robotics programming, combining coding with electronics and mechanical engineering.\n\n**Materials:**\n• Multifunction car robot kit\n• Arduino Uno board\n• Arduino IDE software\n• Motor driver (L298N)\n• Sensors (ultrasonic, IR)\n• Batteries\n• USB cable\n• Computer\n\n**Procedure:**\n1. Assemble robot hardware:\n   - Mount motors and wheels\n   - Connect motor driver to Arduino\n   - Install sensors\n2. Learn Arduino programming:\n   - Setup() and loop() functions\n   - Digital and analog I/O\n   - Motor control with PWM\n3. Write basic movement code:\n```cpp\n// Motor pins\n#define IN1 2\n#define IN2 3\n#define IN3 4\n#define IN4 5\n\nvoid forward() {\n  digitalWrite(IN1, HIGH);\n  digitalWrite(IN2, LOW);\n  digitalWrite(IN3, HIGH);\n  digitalWrite(IN4, LOW);\n}\n\nvoid stop() {\n  digitalWrite(IN1, LOW);\n  digitalWrite(IN2, LOW);\n  digitalWrite(IN3, LOW);\n  digitalWrite(IN4, LOW);\n}\n```\n4. Add sensor reading:\n   - Distance measurement\n   - Line detection\n5. Create autonomous behavior:\n   - Obstacle avoidance\n   - Line following\n6. Test and optimize\n\n**Expected Outcome:** Integrate coding with hardware. Understand sensor-actuator systems. Learn embedded programming. Develop collaboration, design, and innovation skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],

            // ==================== FORM 4 - CODING ====================
            [
                'name' => 'Build a Chatbot',
                'class' => 'Form 4',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn basic concepts of Artificial Intelligence, Machine Learning, and Natural Language Processing.\n\n**Materials:**\n• Computer with internet access\n• Python (or JavaScript)\n• Code editor\n• Chatbot platform (Dialogflow, or build from scratch)\n\n**Procedure:**\n1. Understand chatbot concepts:\n   - Natural Language Processing (NLP)\n   - Intent recognition\n   - Entity extraction\n   - Response generation\n2. Simple rule-based chatbot (Python):\n```python\nresponses = {\n    'hello': 'Hi! How can I help you?',\n    'bye': 'Goodbye! Have a nice day!',\n    'name': 'I am ChatBot 1.0',\n}\n\ndef get_response(user_input):\n    user_input = user_input.lower()\n    for key in responses:\n        if key in user_input:\n            return responses[key]\n    return \"I don't understand. Can you rephrase?\"\n\nwhile True:\n    user = input(\"You: \")\n    if user.lower() == 'quit':\n        break\n    print(\"Bot:\", get_response(user))\n```\n3. Improve with pattern matching\n4. Add more intents and responses\n5. Create context awareness\n6. Integrate with messaging platform (optional)\n7. Explore AI services (Dialogflow, ChatGPT API)\n\n**Expected Outcome:** Understand AI and NLP basics. Learn pattern matching and decision trees. Explore machine learning concepts. Develop critical thinking and innovation skills.",
                'external_url' => 'https://dialogflow.cloud.google.com/',
                'is_active' => true,
            ],
            [
                'name' => 'Fingerprint Door Lock Project',
                'class' => 'Form 4',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn how biometric security systems work and build a smart door lock using fingerprint recognition.\n\n**Materials:**\n• Computer with Arduino IDE\n• Arduino Uno board\n• Fingerprint scanner module (R307 or similar)\n• Servo motor or solenoid lock\n• LEDs (Green, Red)\n• Buzzer\n• Connecting wires\n• Resistors\n• Glue for mounting\n• Power supply\n\n**Procedure:**\n1. Understand biometric security:\n   - How fingerprint scanning works\n   - Template storage and matching\n   - Security considerations\n2. Connect fingerprint module to Arduino:\n   - TX → Pin 2, RX → Pin 3 (Software Serial)\n   - VCC → 5V, GND → GND\n3. Install Adafruit Fingerprint library\n4. Enroll fingerprints:\n```cpp\n#include <Adafruit_Fingerprint.h>\nSoftwareSerial mySerial(2, 3);\nAdafruit_Fingerprint finger = Adafruit_Fingerprint(&mySerial);\n\nvoid enrollFingerprint(int id) {\n  // Follow enrollment process\n  // Store fingerprint template\n}\n```\n5. Program lock mechanism:\n   - Read fingerprint\n   - Compare with stored templates\n   - If match: unlock (servo/solenoid), green LED\n   - If no match: red LED, buzzer alert\n6. Add features:\n   - Multiple user support\n   - Admin mode to add/delete prints\n   - Access log\n7. Mount in enclosure\n8. Test security\n\n**Expected Outcome:** Understand biometric authentication. Learn serial communication. Implement security systems. Develop problem-solving and innovation skills.",
                'external_url' => 'https://www.tinkercad.com/circuits',
                'is_active' => true,
            ],
            [
                'name' => 'Data Science with Python',
                'class' => 'Form 4',
                'subject' => 'Coding',
                'description' => "**Objective:** Learn data analysis and visualization using Python programming.\n\n**Materials:**\n• Computer with Python installed\n• Libraries: pandas, matplotlib, numpy\n• Jupyter Notebook (recommended)\n• Sample datasets (CSV files)\n\n**Procedure:**\n1. Set up environment:\n   - Install Python\n   - Install libraries: pip install pandas matplotlib numpy\n   - Launch Jupyter Notebook\n2. Learn pandas basics:\n```python\nimport pandas as pd\n\n# Load data\ndf = pd.read_csv('students.csv')\n\n# View data\nprint(df.head())\nprint(df.describe())\n\n# Filter data\npassed = df[df['score'] >= 50]\n```\n3. Data cleaning:\n   - Handle missing values\n   - Remove duplicates\n   - Fix data types\n4. Create visualizations:\n```python\nimport matplotlib.pyplot as plt\n\n# Bar chart\ndf['subject'].value_counts().plot(kind='bar')\nplt.title('Students per Subject')\nplt.show()\n\n# Histogram\ndf['score'].hist(bins=10)\nplt.xlabel('Score')\nplt.show()\n```\n5. Analyze real dataset:\n   - School exam results\n   - Weather data\n   - Population statistics\n6. Present findings\n\n**Expected Outcome:** Learn data manipulation with pandas. Create meaningful visualizations. Develop analytical thinking. Apply statistics to real-world data.",
                'external_url' => 'https://www.kaggle.com/learn',
                'is_active' => true,
            ],

            // ==================== FORM 1 - MATHEMATICS ====================
            [
                'name' => 'Play Mathematical Games - Probability',
                'class' => 'Form 1',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Apply probability skills through interactive games using dice, cards, and board games.\n\n**Materials:**\n• Dice (various - 6-sided, 12-sided)\n• Playing cards\n• Monopoly game\n• Chess/Draughts\n• Coins\n• Paper and pencil for recording\n• Calculator\n\n**Procedure:**\n1. DICE PROBABILITY:\n   - Roll a die 60 times, record outcomes\n   - Calculate experimental probability for each number\n   - Compare with theoretical probability (1/6)\n   - Calculate P(even), P(greater than 4), P(prime)\n\n2. COIN TOSS EXPERIMENT:\n   - Toss coin 50 times, record heads/tails\n   - Calculate P(heads), P(tails)\n   - Try two coins: P(both heads), P(one each)\n\n3. CARD PROBABILITY:\n   - Calculate P(drawing an Ace) = 4/52\n   - P(red card), P(face card), P(heart)\n   - Conditional probability exercises\n\n4. BOARD GAMES:\n   - Play Monopoly - calculate expected moves\n   - Analyze winning strategies mathematically\n\n**Mathematical Concepts:**\n- Probability = Favorable outcomes / Total outcomes\n- Experimental vs Theoretical probability\n- Sample space and events\n\n**Expected Outcome:** Understand probability concepts through hands-on experience. Calculate probabilities accurately. Apply mathematical thinking to games and real-life situations.",
                'external_url' => 'https://filbertayo.edu/en/simulations/plinko-probability',
                'is_active' => true,
            ],
            [
                'name' => 'Cake Making - Measurement Project',
                'class' => 'Form 1',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Understand measurements in volume, mass, and surface area through practical baking.\n\n**Materials:**\n• Flour (500g)\n• Eggs (4)\n• Sugar (250g)\n• Butter/Margarine (250g)\n• Milk (100ml)\n• Baking powder\n• Measuring cups and spoons\n• Kitchen scale\n• Baking pan (various sizes)\n• Mixing bowl\n• Oven\n\n**Procedure:**\n1. MEASUREMENT CONVERSIONS:\n   - Convert recipe from grams to cups\n   - Convert temperatures: Celsius ↔ Fahrenheit\n   - F = (C × 9/5) + 32\n\n2. RATIO AND PROPORTION:\n   - Original recipe serves 8 people\n   - Calculate ingredients for 12 people (multiply by 1.5)\n   - Calculate for 4 people (divide by 2)\n\n3. VOLUME CALCULATIONS:\n   - Calculate volume of round pan: V = πr²h\n   - Calculate volume of rectangular pan: V = l × w × h\n   - Determine if batter fits the pan\n\n4. SURFACE AREA (for frosting):\n   - Round cake: A = 2πr² + 2πrh\n   - Rectangular: A = 2(lw + lh + wh)\n   - Calculate amount of frosting needed\n\n5. TIME CALCULATIONS:\n   - Prep time + Bake time + Cooling time\n   - Plan backwards from serving time\n\n**Expected Outcome:** Apply measurement skills practically. Understand ratios and proportions. Calculate volumes and surface areas. Develop problem-solving and collaboration skills.",
                'external_url' => 'https://www.mathsisfun.com/measure/index.html',
                'is_active' => true,
            ],

            // ==================== FORM 2 - MATHEMATICS ====================
            [
                'name' => 'Design a Playground Project',
                'class' => 'Form 2',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Apply measurement and geometry skills to design a school playground, calculating areas and costs.\n\n**Materials:**\n• Graph paper\n• Ruler and measuring tape\n• Colored pencils/markers\n• Calculator\n• Price lists for materials\n• Cardboard for model (optional)\n\n**Procedure:**\n1. SURVEY THE SPACE:\n   - Measure available area (length × width)\n   - Calculate total area in m²\n   - Identify obstacles and boundaries\n\n2. DESIGN LAYOUT:\n   - Draw to scale (e.g., 1cm = 1m)\n   - Include: swings, slides, sandbox, paths, green areas\n   - Calculate area for each feature\n\n3. GEOMETRY CALCULATIONS:\n   - Rectangular areas: A = l × w\n   - Circular sandbox: A = πr²\n   - Triangular garden: A = ½ × b × h\n   - Perimeter for fencing: P = 2(l + w)\n\n4. COST ESTIMATION:\n   - Grass: TSh 5,000/m²\n   - Sand: TSh 3,000/m²\n   - Fencing: TSh 15,000/m\n   - Equipment costs (research prices)\n   - Total budget calculation\n\n5. CREATE BUDGET SHEET:\n| Item | Quantity | Unit Price | Total |\n|------|----------|------------|-------|\n| Grass | 50m² | 5,000 | 250,000 |\n\n6. Present design with justifications\n\n**Expected Outcome:** Apply geometry to real-world design. Calculate areas and perimeters accurately. Develop financial math skills. Practice problem-solving and communication.",
                'external_url' => 'https://www.geogebra.org/',
                'is_active' => true,
            ],
            [
                'name' => 'Arts and Mathematical Drawings',
                'class' => 'Form 2',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Combine art and mathematics through geometric designs, patterns, and graphic design principles.\n\n**Materials:**\n• Graph paper\n• Compass and protractor\n• Ruler and set squares\n• Colored pencils/markers\n• Computer with design software (optional)\n• 3D printer (if available)\n\n**Procedure:**\n1. GEOMETRIC PATTERNS:\n   - Create tessellations using regular polygons\n   - Design Islamic geometric patterns\n   - Explore which shapes tessellate and why\n\n2. SYMMETRY AND TRANSFORMATIONS:\n   - Line symmetry designs\n   - Rotational symmetry patterns\n   - Translations and reflections\n\n3. GOLDEN RATIO IN ART:\n   - Learn φ (phi) = 1.618...\n   - Draw golden rectangle\n   - Create golden spiral\n   - Find golden ratio in nature and art\n\n4. COORDINATE GEOMETRY ART:\n   - Plot points on coordinate plane\n   - Connect points to create pictures\n   - Design logos using geometric shapes\n\n5. FRACTALS:\n   - Draw Sierpinski triangle\n   - Create Koch snowflake\n   - Understand self-similarity\n\n6. 3D DESIGN (Advanced):\n   - Sketch 3D objects (isometric drawing)\n   - Calculate dimensions for 3D printing\n   - Create nets for 3D shapes\n\n**Expected Outcome:** Understand geometry in art and design. Apply measurement and construction skills. Develop creativity and spatial reasoning. Learn graphic design principles.",
                'external_url' => 'https://www.geogebra.org/',
                'is_active' => true,
            ],

            // ==================== FORM 3 - MATHEMATICS ====================
            [
                'name' => 'Plan a Trip - Budget Mathematics',
                'class' => 'Form 3',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Apply geometry, measurement, and financial mathematics to plan a complete trip.\n\n**Materials:**\n• Maps (physical or Google Maps)\n• Calculator\n• Microsoft Excel or spreadsheet\n• Internet for research\n• Budget planning sheets\n\n**Procedure:**\n1. CHOOSE DESTINATION:\n   - Select a city/national park to visit\n   - Research using internet/maps\n\n2. DISTANCE AND ROUTE CALCULATIONS:\n   - Use map scale to calculate distances\n   - Calculate total journey distance\n   - Compare different routes (shortest vs scenic)\n   - Use Pythagoras for direct distance\n\n3. TIME CALCULATIONS:\n   - Time = Distance ÷ Speed\n   - Calculate travel time at different speeds\n   - Plan departure time to arrive by specific hour\n   - Account for rest stops\n\n4. BUDGET PLANNING:\n   - Transportation costs:\n     * Fuel: (Distance ÷ km per liter) × price per liter\n     * Bus/train tickets\n   - Accommodation: price × nights\n   - Food: daily allowance × days\n   - Entertainment: entrance fees, activities\n   - Emergency fund: 10% of total\n\n5. CREATE EXCEL SPREADSHEET:\n   - Use formulas for calculations\n   - Create pie chart of expenses\n   - Compare costs for different scenarios\n\n6. PROBABILITY/CONTINGENCY:\n   - Weather probability for outdoor activities\n   - Backup plans and costs\n\n**Expected Outcome:** Apply multiple math concepts to real planning. Master distance, time, and speed calculations. Develop financial literacy and spreadsheet skills.",
                'external_url' => 'https://www.google.com/maps',
                'is_active' => true,
            ],
            [
                'name' => 'Build a Bridge - Engineering Mathematics',
                'class' => 'Form 3',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Apply mathematical skills to calculate dimensions, materials, and test structural strength of a model bridge.\n\n**Materials:**\n• Popsicle sticks or wooden dowels\n• Glue (wood glue or hot glue)\n• String/wire for suspension\n• Ruler and measuring tape\n• Weights for testing\n• Graph paper for design\n• Calculator\n\n**Procedure:**\n1. RESEARCH BRIDGE TYPES:\n   - Beam, Arch, Truss, Suspension\n   - Identify mathematical shapes in each\n   - Choose design for your bridge\n\n2. DESIGN CALCULATIONS:\n   - Span length (distance between supports)\n   - Height requirements\n   - Scale factor for model (e.g., 1:100)\n   - Calculate actual dimensions from scale drawing\n\n3. GEOMETRY IN BRIDGES:\n   - Triangles for strength (trusses)\n   - Calculate angles using trigonometry\n   - sin θ = opposite/hypotenuse\n   - cos θ = adjacent/hypotenuse\n   - Arc measurements for arch bridges\n\n4. MATERIAL CALCULATIONS:\n   - Count pieces needed\n   - Calculate total length of materials\n   - Estimate glue/connectors required\n   - Cost analysis\n\n5. BUILD AND TEST:\n   - Construct following design\n   - Test with increasing weights\n   - Record maximum load\n   - Calculate efficiency: Load supported ÷ Bridge weight\n\n6. ANALYZE RESULTS:\n   - Graph load vs deflection\n   - Compare with other designs\n   - Identify failure points\n\n**Expected Outcome:** Apply geometry and trigonometry practically. Calculate dimensions and materials. Understand structural mathematics. Develop problem-solving and teamwork skills.",
                'external_url' => 'https://www.geogebra.org/',
                'is_active' => true,
            ],

            // ==================== FORM 4 - MATHEMATICS ====================
            [
                'name' => 'Collecting and Analyzing Data - Statistics Project',
                'class' => 'Form 4',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Apply research, statistical, and graphical skills to collect, analyze, and present real-world data.\n\n**Materials:**\n• Computer with Excel/Google Sheets\n• Questionnaires\n• Notebooks and pens\n• Internet access\n• Calculator\n• Graph paper\n\n**Procedure:**\n1. CHOOSE A TOPIC:\n   - Examples: Student study habits, Phone usage, Health behaviors\n   - Define research question\n   - Identify population and sample\n\n2. DESIGN QUESTIONNAIRE:\n   - Create clear, unbiased questions\n   - Include multiple choice and numerical data\n   - Pilot test with small group\n\n3. COLLECT DATA:\n   - Survey at least 50 respondents\n   - Ensure random sampling\n   - Record data systematically\n\n4. ORGANIZE DATA:\n   - Create frequency tables\n   - Group data into class intervals\n   - Enter into spreadsheet\n\n5. CALCULATE STATISTICS:\n   - Mean: x̄ = Σx ÷ n\n   - Median: middle value\n   - Mode: most frequent value\n   - Range: max - min\n   - Standard deviation (Excel: =STDEV())\n\n6. CREATE VISUALIZATIONS:\n   - Bar charts for categorical data\n   - Histograms for continuous data\n   - Pie charts for proportions\n   - Scatter plots for relationships\n   - Line graphs for trends\n\n7. ANALYZE AND INTERPRET:\n   - What does the data show?\n   - Are there correlations?\n   - What conclusions can be drawn?\n   - Limitations of the study\n\n8. PRESENT FINDINGS:\n   - Create report or presentation\n   - Include all charts and calculations\n   - Discuss implications\n\n**Expected Outcome:** Master statistical calculations. Create meaningful data visualizations. Develop research and analytical skills. Apply mathematics to real-world problems.",
                'external_url' => 'https://www.geogebra.org/statistics',
                'is_active' => true,
            ],
            [
                'name' => 'Linear Programming - Optimization Problems',
                'class' => 'Form 4',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Apply linear programming to solve real-world optimization problems in business and resource allocation.\n\n**Materials:**\n• Graph paper\n• Ruler\n• Calculator\n• Computer with GeoGebra or Excel Solver (optional)\n\n**Procedure:**\n1. UNDERSTAND THE CONCEPT:\n   - Objective function (what to maximize/minimize)\n   - Constraints (limitations)\n   - Decision variables\n\n2. EXAMPLE PROBLEM - PRODUCTION:\n   A factory makes chairs and tables.\n   - Chair: 2 hours carpentry, 1 hour painting, profit TSh 5,000\n   - Table: 3 hours carpentry, 2 hours painting, profit TSh 8,000\n   - Available: 120 hours carpentry, 80 hours painting\n   - How many of each to maximize profit?\n\n3. SET UP MATHEMATICALLY:\n   Let x = chairs, y = tables\n   - Maximize: P = 5000x + 8000y\n   - Subject to:\n     * 2x + 3y ≤ 120 (carpentry)\n     * x + 2y ≤ 80 (painting)\n     * x ≥ 0, y ≥ 0\n\n4. GRAPH THE CONSTRAINTS:\n   - Plot each inequality\n   - Identify feasible region\n   - Find corner points\n\n5. FIND OPTIMAL SOLUTION:\n   - Evaluate objective function at each corner\n   - Maximum/minimum occurs at a vertex\n   - State the solution\n\n6. REAL-WORLD APPLICATIONS:\n   - Diet problems (minimize cost)\n   - Transportation (minimize distance)\n   - Investment (maximize returns)\n\n**Expected Outcome:** Formulate optimization problems mathematically. Graph systems of inequalities. Find optimal solutions. Apply to business and resource decisions.",
                'external_url' => 'https://www.geogebra.org/',
                'is_active' => true,
            ],
            [
                'name' => 'Financial Mathematics - Interest and Investments',
                'class' => 'Form 4',
                'subject' => 'Mathematics',
                'description' => "**Objective:** Apply mathematical skills to understand compound interest, loans, investments, and financial planning.\n\n**Materials:**\n• Calculator (scientific)\n• Spreadsheet software (Excel)\n• Financial tables\n• Paper and pencil\n\n**Procedure:**\n1. SIMPLE INTEREST:\n   - Formula: I = PRT (Principal × Rate × Time)\n   - Calculate interest on savings\n   - Calculate total amount: A = P + I\n\n2. COMPOUND INTEREST:\n   - Formula: A = P(1 + r/n)^(nt)\n   - Compare monthly vs yearly compounding\n   - Calculate effective annual rate\n\n3. LOAN CALCULATIONS:\n   - Monthly payment formula\n   - Create amortization schedule in Excel\n   - Calculate total interest paid\n   - Compare loan terms (5yr vs 10yr)\n\n4. INVESTMENT ANALYSIS:\n   - Future value of regular savings\n   - Present value calculations\n   - Compare investment options\n   - Risk vs return analysis\n\n5. PRACTICAL EXERCISES:\n   - Calculate mortgage payments\n   - Plan retirement savings\n   - Compare phone payment plans\n   - Analyze \"buy now pay later\" schemes\n\n6. CREATE FINANCIAL PLAN:\n   - Set a savings goal\n   - Calculate required monthly savings\n   - Project growth over time\n   - Visualize with graphs\n\n**Example Calculation:**\nInvest TSh 100,000 at 8% compounded monthly for 5 years:\nA = 100,000(1 + 0.08/12)^(12×5)\nA = 100,000(1.00667)^60\nA ≈ TSh 148,985\n\n**Expected Outcome:** Master compound interest calculations. Understand loan mathematics. Make informed financial decisions. Develop practical money management skills.",
                'external_url' => 'https://www.investor.gov/financial-tools-calculators',
                'is_active' => true,
            ],
        ];

        foreach ($experiments as $experiment) {
            $classId = $classes[$experiment['class']] ?? null;
            $subjectId = $subjects[$experiment['subject']] ?? null;

            if ($classId) {
                VrExperiment::updateOrCreate(
                    [
                        'name' => $experiment['name'],
                        'school_class_id' => $classId,
                    ],
                    [
                        'name' => $experiment['name'],
                        'description' => $experiment['description'],
                        'external_url' => $experiment['external_url'],
                        'thumbnail' => null,
                        'school_class_id' => $classId,
                        'subject_id' => $subjectId,
                        'is_active' => $experiment['is_active'],
                    ]
                );
            }
        }
    }
}

