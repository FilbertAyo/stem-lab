<?php

namespace Database\Seeders;

use App\Models\VrExperiment;
use App\Models\VrQuestion;
use App\Models\VrQuestionBank;
use Illuminate\Database\Seeder;

class VrAssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $experiments = VrExperiment::all()->keyBy('name');

        $assessments = [
            // ==================== PHYSICS ASSESSMENTS ====================
            
            // Archimedes Principle
            [
                'experiment' => 'Archimedes Principle Experiment',
                'bank' => [
                    'title' => 'Archimedes Principle Quiz',
                    'description' => 'Test your understanding of buoyancy and Archimedes\' Principle after completing the virtual experiment.',
                    'time_limit' => 15,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'According to Archimedes\' Principle, when a body is immersed in a fluid, it experiences:',
                        'options' => ['An upthrust equal to its own weight', 'An upthrust equal to the weight of fluid displaced', 'A downward force equal to the weight of fluid displaced', 'No force from the fluid'],
                        'correct_answer' => 'An upthrust equal to the weight of fluid displaced',
                        'explanation' => 'Archimedes\' Principle states that the upthrust (buoyant force) on a submerged object equals the weight of the fluid displaced by that object.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'A metal block weighs 50N in air and 35N when submerged in water. What is the upthrust on the block?',
                        'options' => ['85N', '50N', '35N', '15N'],
                        'correct_answer' => '15N',
                        'explanation' => 'Upthrust = Weight in air - Weight in water = 50N - 35N = 15N',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'The Eureka can is used to collect the displaced water when an object is submerged.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'The Eureka (displacement) can has a spout that allows displaced water to overflow and be collected for measurement.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'An object floats when:',
                        'options' => ['Its weight is greater than the upthrust', 'Its weight equals the upthrust', 'Its density is greater than the fluid', 'The upthrust is zero'],
                        'correct_answer' => 'Its weight equals the upthrust',
                        'explanation' => 'For a floating object, the upthrust exactly balances the weight, resulting in equilibrium.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'short_answer',
                        'question_text' => 'What instrument is used to measure the weight of an object in air and water during this experiment?',
                        'options' => null,
                        'correct_answer' => 'spring balance',
                        'explanation' => 'A spring balance measures force/weight and can be used to weigh objects both in air and while submerged in water.',
                        'points' => 1,
                    ],
                ],
            ],

            // Static Electricity
            [
                'experiment' => 'Static Electricity - Charging by Friction',
                'bank' => [
                    'title' => 'Static Electricity Assessment',
                    'description' => 'Evaluate your knowledge of static charges and charging by friction.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'When a plastic rod is rubbed with fur, the rod becomes:',
                        'options' => ['Positively charged', 'Negatively charged', 'Neutral', 'Magnetic'],
                        'correct_answer' => 'Negatively charged',
                        'explanation' => 'Plastic (ebonite) gains electrons from fur when rubbed, becoming negatively charged.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Two objects with the same type of charge will:',
                        'options' => ['Attract each other', 'Repel each other', 'Have no effect on each other', 'Become neutral'],
                        'correct_answer' => 'Repel each other',
                        'explanation' => 'Like charges repel and opposite charges attract. This is a fundamental law of electrostatics.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'A charged rod can attract small pieces of paper because paper becomes polarized (induced charge).',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Even neutral objects can be attracted to charged objects because the charged object induces a temporary separation of charges in the neutral object.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Charging by friction involves the transfer of:',
                        'options' => ['Protons', 'Neutrons', 'Electrons', 'Atoms'],
                        'correct_answer' => 'Electrons',
                        'explanation' => 'Only electrons are transferred during charging by friction. Protons remain fixed in the nucleus.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'When a glass rod is rubbed with silk, the glass rod becomes:',
                        'options' => ['Negatively charged', 'Positively charged', 'Neutral', 'An insulator'],
                        'correct_answer' => 'Positively charged',
                        'explanation' => 'Glass loses electrons to silk when rubbed, leaving the glass with a positive charge.',
                        'points' => 1,
                    ],
                ],
            ],

            // Series and Parallel Circuits
            [
                'experiment' => 'Series and Parallel Circuits',
                'bank' => [
                    'title' => 'Electric Circuits Quiz',
                    'description' => 'Test your understanding of series and parallel circuit configurations.',
                    'time_limit' => 15,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'In a series circuit, the current:',
                        'options' => ['Is different at different points', 'Is the same throughout', 'Is zero', 'Depends on voltage only'],
                        'correct_answer' => 'Is the same throughout',
                        'explanation' => 'In a series circuit, there is only one path for current to flow, so the same current passes through all components.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'In a parallel circuit, the voltage across each branch is:',
                        'options' => ['Different', 'The same', 'Zero', 'Double the source voltage'],
                        'correct_answer' => 'The same',
                        'explanation' => 'In parallel circuits, each branch is connected directly to the power source, so they all have the same voltage.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'Bulbs in a parallel circuit shine brighter than bulbs in a series circuit (with the same number of bulbs).',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'In parallel, each bulb receives the full voltage. In series, voltage is divided among bulbs, so they receive less voltage and shine dimmer.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'If one bulb in a series circuit burns out, the other bulbs will:',
                        'options' => ['Shine brighter', 'Shine dimmer', 'Go out completely', 'Be unaffected'],
                        'correct_answer' => 'Go out completely',
                        'explanation' => 'In series circuits, there is only one path. If any component fails, the circuit breaks and all components stop working.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The total resistance in a series circuit is:',
                        'options' => ['Less than the smallest resistor', 'Equal to the largest resistor', 'The sum of all resistances', 'The product of all resistances'],
                        'correct_answer' => 'The sum of all resistances',
                        'explanation' => 'In series: R_total = R1 + R2 + R3 + ... The resistances add up directly.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'short_answer',
                        'question_text' => 'What instrument is used to measure current in a circuit?',
                        'options' => null,
                        'correct_answer' => 'ammeter',
                        'explanation' => 'An ammeter measures electric current and must be connected in series with the circuit.',
                        'points' => 1,
                    ],
                ],
            ],

            // Magnetic Field Lines
            [
                'experiment' => 'Magnetic Field Lines',
                'bank' => [
                    'title' => 'Magnetism Quiz',
                    'description' => 'Assess your understanding of magnetic fields and field patterns.',
                    'time_limit' => 10,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Magnetic field lines around a bar magnet:',
                        'options' => ['Start from the South pole and end at the North pole', 'Start from the North pole and end at the South pole', 'Form straight lines', 'Do not exist'],
                        'correct_answer' => 'Start from the North pole and end at the South pole',
                        'explanation' => 'By convention, magnetic field lines emerge from the North pole and enter the South pole outside the magnet.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'Iron filings can be used to visualize magnetic field patterns.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Iron filings align with the magnetic field, making the invisible field lines visible.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The magnetic field is strongest at:',
                        'options' => ['The middle of the magnet', 'The poles', 'Equally strong everywhere', 'Outside the magnet only'],
                        'correct_answer' => 'The poles',
                        'explanation' => 'Field lines are closest together at the poles, indicating the strongest field strength.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Magnetic field lines:',
                        'options' => ['Can cross each other', 'Never cross each other', 'Always form straight lines', 'Only exist inside magnets'],
                        'correct_answer' => 'Never cross each other',
                        'explanation' => 'If field lines crossed, it would mean the field points in two directions at one point, which is impossible.',
                        'points' => 1,
                    ],
                ],
            ],

            // Principle of Moments
            [
                'experiment' => 'Principle of Moments',
                'bank' => [
                    'title' => 'Moments and Levers Quiz',
                    'description' => 'Test your knowledge of the principle of moments and equilibrium.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The moment of a force is calculated as:',
                        'options' => ['Force × Time', 'Force × Distance from pivot', 'Force ÷ Distance', 'Mass × Acceleration'],
                        'correct_answer' => 'Force × Distance from pivot',
                        'explanation' => 'Moment (torque) = Force × Perpendicular distance from the pivot point.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'For a beam to be balanced, the sum of clockwise moments must equal:',
                        'options' => ['Zero', 'The sum of anticlockwise moments', 'The total weight', 'The distance from pivot'],
                        'correct_answer' => 'The sum of anticlockwise moments',
                        'explanation' => 'This is the principle of moments: for equilibrium, clockwise moments = anticlockwise moments.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'A 20N weight is placed 30cm from the pivot. What weight at 60cm will balance it?',
                        'options' => ['40N', '20N', '10N', '5N'],
                        'correct_answer' => '10N',
                        'explanation' => '20N × 30cm = W × 60cm, so W = 600/60 = 10N',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'Moving a weight farther from the pivot increases its moment.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Since Moment = Force × Distance, increasing distance increases the moment.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'short_answer',
                        'question_text' => 'What is the SI unit of moment?',
                        'options' => null,
                        'correct_answer' => 'Nm',
                        'explanation' => 'The SI unit of moment is Newton-meter (Nm), sometimes written as N·m.',
                        'points' => 1,
                    ],
                ],
            ],

            // Concave Mirror
            [
                'experiment' => 'Concave Mirror - Focal Length',
                'bank' => [
                    'title' => 'Reflection and Mirrors Quiz',
                    'description' => 'Evaluate your understanding of concave mirrors and image formation.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The focal length of a concave mirror is related to its radius of curvature by:',
                        'options' => ['f = R', 'f = 2R', 'f = R/2', 'f = R²'],
                        'correct_answer' => 'f = R/2',
                        'explanation' => 'The focal length is half the radius of curvature for a spherical mirror.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'When a distant object is used, the image forms at:',
                        'options' => ['The center of curvature', 'The focal point', 'Infinity', 'The pole'],
                        'correct_answer' => 'The focal point',
                        'explanation' => 'Parallel rays from a distant object converge at the focal point of a concave mirror.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'A concave mirror always produces a virtual image.',
                        'options' => null,
                        'correct_answer' => 'False',
                        'explanation' => 'Concave mirrors produce real images when the object is beyond the focal point, and virtual images when the object is between the focal point and mirror.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'When an object is placed between the focal point and the pole of a concave mirror, the image is:',
                        'options' => ['Real, inverted, diminished', 'Virtual, upright, magnified', 'Real, upright, magnified', 'No image formed'],
                        'correct_answer' => 'Virtual, upright, magnified',
                        'explanation' => 'This is how concave mirrors are used as makeup/shaving mirrors - they magnify when object is close.',
                        'points' => 1,
                    ],
                ],
            ],

            // Refraction
            [
                'experiment' => 'Refraction and Refractive Index',
                'bank' => [
                    'title' => 'Refraction and Snell\'s Law Quiz',
                    'description' => 'Test your knowledge of light refraction and refractive index.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'When light travels from air into glass, it:',
                        'options' => ['Bends away from the normal', 'Bends toward the normal', 'Does not bend', 'Reflects completely'],
                        'correct_answer' => 'Bends toward the normal',
                        'explanation' => 'Light bends toward the normal when entering a denser medium (higher refractive index).',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Snell\'s Law states that:',
                        'options' => ['sin(i) = sin(r)', 'sin(i)/sin(r) = constant', 'i = r', 'i + r = 90°'],
                        'correct_answer' => 'sin(i)/sin(r) = constant',
                        'explanation' => 'Snell\'s Law: n = sin(i)/sin(r), where n is the refractive index.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'The refractive index of glass is approximately 1.5.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Common glass has a refractive index of about 1.5, meaning light travels 1.5 times slower in glass than in vacuum.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'If the angle of incidence is 0° (ray perpendicular to surface), the angle of refraction is:',
                        'options' => ['90°', '45°', '0°', 'Depends on the medium'],
                        'correct_answer' => '0°',
                        'explanation' => 'A ray perpendicular to the surface passes through without bending.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'short_answer',
                        'question_text' => 'What is the name of the line perpendicular to the surface at the point of incidence?',
                        'options' => null,
                        'correct_answer' => 'normal',
                        'explanation' => 'The normal is an imaginary line perpendicular to the surface, used as a reference for measuring angles.',
                        'points' => 1,
                    ],
                ],
            ],

            // Electromagnetic Induction
            [
                'experiment' => 'Electromagnetic Induction',
                'bank' => [
                    'title' => 'Electromagnetic Induction Quiz',
                    'description' => 'Assess your understanding of Faraday\'s Law and induced EMF.',
                    'time_limit' => 15,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Electromagnetic induction occurs when:',
                        'options' => ['A magnet is stationary inside a coil', 'A magnet moves relative to a coil', 'A battery is connected to a coil', 'Two magnets are placed together'],
                        'correct_answer' => 'A magnet moves relative to a coil',
                        'explanation' => 'A changing magnetic field (caused by relative motion) is required to induce an EMF.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The magnitude of induced EMF increases when:',
                        'options' => ['The magnet moves slower', 'The magnet moves faster', 'The coil has fewer turns', 'The magnet is weaker'],
                        'correct_answer' => 'The magnet moves faster',
                        'explanation' => 'According to Faraday\'s Law, faster change in magnetic flux produces greater induced EMF.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'When the direction of magnet movement is reversed, the direction of induced current also reverses.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'The direction of induced current depends on whether magnetic flux is increasing or decreasing.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'A galvanometer in this experiment is used to:',
                        'options' => ['Measure resistance', 'Generate magnetic field', 'Detect induced current', 'Store electrical energy'],
                        'correct_answer' => 'Detect induced current',
                        'explanation' => 'A galvanometer is a sensitive instrument that detects small currents and shows their direction.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Lenz\'s Law states that induced current flows in a direction to:',
                        'options' => ['Maximize the change in flux', 'Oppose the change producing it', 'Attract the magnet', 'Increase the magnetic field'],
                        'correct_answer' => 'Oppose the change producing it',
                        'explanation' => 'Lenz\'s Law is about conservation of energy - the induced current opposes the change that causes it.',
                        'points' => 1,
                    ],
                ],
            ],

            // Simple Pendulum
            [
                'experiment' => 'Simple Pendulum - Measuring g',
                'bank' => [
                    'title' => 'Simple Pendulum Quiz',
                    'description' => 'Test your knowledge of pendulum motion and determining gravitational acceleration.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The period of a simple pendulum depends on:',
                        'options' => ['Mass of the bob', 'Length of string and gravity', 'Amplitude only', 'Material of the string'],
                        'correct_answer' => 'Length of string and gravity',
                        'explanation' => 'T = 2π√(L/g). Period depends only on length and gravitational acceleration (for small angles).',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'If the length of a pendulum is quadrupled, the period will:',
                        'options' => ['Double', 'Quadruple', 'Halve', 'Remain the same'],
                        'correct_answer' => 'Double',
                        'explanation' => 'Since T ∝ √L, if L becomes 4L, T becomes √4 × T = 2T.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'The mass of the pendulum bob affects its period of oscillation.',
                        'options' => null,
                        'correct_answer' => 'False',
                        'explanation' => 'The period of a simple pendulum is independent of the mass. Only length and gravity matter.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Why do we time 20 oscillations instead of just one?',
                        'options' => ['To make the experiment longer', 'To reduce timing errors', 'The pendulum moves too fast', 'It\'s required by law'],
                        'correct_answer' => 'To reduce timing errors',
                        'explanation' => 'Timing multiple oscillations reduces the percentage error in reaction time.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'When plotting T² against L, the slope of the graph equals:',
                        'options' => ['g', '2π/g', '4π²/g', 'g/4π²'],
                        'correct_answer' => '4π²/g',
                        'explanation' => 'From T² = (4π²/g)L, the slope is 4π²/g.',
                        'points' => 1,
                    ],
                ],
            ],

            // ==================== CHEMISTRY ASSESSMENTS ====================

            // Oxygen Preparation
            [
                'experiment' => 'Preparation and Properties of Oxygen',
                'bank' => [
                    'title' => 'Oxygen Gas Quiz',
                    'description' => 'Test your knowledge of oxygen preparation and its properties.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'In the laboratory, oxygen is commonly prepared from hydrogen peroxide using:',
                        'options' => ['Sodium chloride as catalyst', 'Manganese dioxide as catalyst', 'Copper sulfate as catalyst', 'No catalyst needed'],
                        'correct_answer' => 'Manganese dioxide as catalyst',
                        'explanation' => 'MnO₂ acts as a catalyst to speed up the decomposition of H₂O₂ into water and oxygen.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Oxygen gas can be tested by:',
                        'options' => ['A burning splint - it goes out', 'A glowing splint - it relights', 'Limewater - it turns milky', 'Litmus paper - it turns red'],
                        'correct_answer' => 'A glowing splint - it relights',
                        'explanation' => 'Oxygen supports combustion, so a glowing (not burning) splint will relight in oxygen.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'When sulfur burns in oxygen, it produces an acidic oxide.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Sulfur burns to form sulfur dioxide (SO₂), which dissolves in water to form sulfurous acid.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Oxygen is collected by downward displacement of water because:',
                        'options' => ['It is lighter than air', 'It is heavier than air', 'It is slightly soluble in water', 'It reacts with air'],
                        'correct_answer' => 'It is slightly soluble in water',
                        'explanation' => 'Oxygen is only slightly soluble in water, making water displacement an effective collection method.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'When magnesium burns in oxygen, it produces:',
                        'options' => ['Magnesium hydroxide', 'Magnesium oxide', 'Magnesium chloride', 'Magnesium carbonate'],
                        'correct_answer' => 'Magnesium oxide',
                        'explanation' => '2Mg + O₂ → 2MgO. The bright white flame produces magnesium oxide, a basic oxide.',
                        'points' => 1,
                    ],
                ],
            ],

            // Electrolysis
            [
                'experiment' => 'Electrolysis of Different Electrolytes',
                'bank' => [
                    'title' => 'Electrolysis Quiz',
                    'description' => 'Assess your understanding of electrolysis and the decomposition of electrolytes.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'During electrolysis of water, hydrogen is produced at the:',
                        'options' => ['Anode', 'Cathode', 'Both electrodes', 'Neither electrode'],
                        'correct_answer' => 'Cathode',
                        'explanation' => 'Hydrogen ions (H⁺) gain electrons at the cathode (negative electrode) to form hydrogen gas.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The ratio of hydrogen to oxygen produced by volume is:',
                        'options' => ['1:1', '1:2', '2:1', '3:1'],
                        'correct_answer' => '2:1',
                        'explanation' => '2H₂O → 2H₂ + O₂. Two volumes of hydrogen are produced for every one volume of oxygen.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'Pure water is a poor conductor of electricity.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Pure water has very few ions, so acid or base is added as an electrolyte to increase conductivity.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The gas produced at the anode during electrolysis of water is:',
                        'options' => ['Hydrogen', 'Oxygen', 'Carbon dioxide', 'Chlorine'],
                        'correct_answer' => 'Oxygen',
                        'explanation' => 'Hydroxide ions or water molecules lose electrons at the anode to form oxygen gas.',
                        'points' => 1,
                    ],
                ],
            ],

            // pH Scale
            [
                'experiment' => 'Acids and Bases - pH Testing',
                'bank' => [
                    'title' => 'Acids, Bases, and pH Quiz',
                    'description' => 'Test your knowledge of the pH scale and chemical indicators.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'A solution with pH 3 is:',
                        'options' => ['Neutral', 'Weakly acidic', 'Strongly acidic', 'Basic'],
                        'correct_answer' => 'Strongly acidic',
                        'explanation' => 'pH below 7 is acidic, and the lower the pH, the stronger the acid. pH 3 is strongly acidic.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'A neutral solution has a pH of:',
                        'options' => ['0', '7', '14', '1'],
                        'correct_answer' => '7',
                        'explanation' => 'pH 7 is neutral, where [H⁺] = [OH⁻] = 10⁻⁷ M at 25°C.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'Blue litmus paper turns red in acidic solutions.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Blue litmus turns red in acids. Red litmus turns blue in bases.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Universal indicator shows different colors at different pH values. Which color indicates a strong base?',
                        'options' => ['Red', 'Orange', 'Green', 'Purple/Blue'],
                        'correct_answer' => 'Purple/Blue',
                        'explanation' => 'Universal indicator is red in strong acids, green at pH 7, and purple/blue in strong bases.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'If a solution has pH 12, it is:',
                        'options' => ['Acidic', 'Neutral', 'Weakly basic', 'Strongly basic'],
                        'correct_answer' => 'Strongly basic',
                        'explanation' => 'pH above 7 is basic/alkaline. pH 12 is strongly basic.',
                        'points' => 1,
                    ],
                ],
            ],

            // ==================== BIOLOGY ASSESSMENTS ====================

            // Photosynthesis
            [
                'experiment' => 'Photosynthesis Investigation',
                'bank' => [
                    'title' => 'Photosynthesis Quiz',
                    'description' => 'Test your understanding of photosynthesis and starch testing.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Before testing for starch, a leaf is boiled in alcohol to:',
                        'options' => ['Kill the leaf', 'Remove chlorophyll', 'Add color', 'Make it soft'],
                        'correct_answer' => 'Remove chlorophyll',
                        'explanation' => 'Chlorophyll is green and would mask the iodine color change, so it must be removed first.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Iodine solution turns blue-black in the presence of:',
                        'options' => ['Glucose', 'Starch', 'Protein', 'Fat'],
                        'correct_answer' => 'Starch',
                        'explanation' => 'The iodine test is specific for starch, producing a characteristic blue-black color.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'A variegated leaf (with white and green parts) will test positive for starch only in the green areas.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Only green parts contain chlorophyll and can photosynthesize to produce starch.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Why is the leaf first boiled in water before alcohol?',
                        'options' => ['To remove starch', 'To kill cells and stop reactions', 'To add color', 'To make it dry'],
                        'correct_answer' => 'To kill cells and stop reactions',
                        'explanation' => 'Boiling in water kills the leaf and makes cell membranes permeable for the alcohol treatment.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The equation for photosynthesis is:',
                        'options' => ['6CO₂ + 6H₂O → C₆H₁₂O₆ + 6O₂', 'C₆H₁₂O₆ + 6O₂ → 6CO₂ + 6H₂O', '6O₂ + 6H₂O → C₆H₁₂O₆ + 6CO₂', 'CO₂ + H₂O → CH₄ + O₂'],
                        'correct_answer' => '6CO₂ + 6H₂O → C₆H₁₂O₆ + 6O₂',
                        'explanation' => 'Photosynthesis converts carbon dioxide and water into glucose and oxygen using light energy.',
                        'points' => 1,
                    ],
                ],
            ],

            // Cell Structure
            [
                'experiment' => 'Microscope Use and Cell Observation',
                'bank' => [
                    'title' => 'Cell Structure Quiz',
                    'description' => 'Assess your knowledge of cell structure and microscopy.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Which structure is found in plant cells but NOT in animal cells?',
                        'options' => ['Nucleus', 'Cell membrane', 'Cell wall', 'Cytoplasm'],
                        'correct_answer' => 'Cell wall',
                        'explanation' => 'Plant cells have a rigid cell wall made of cellulose; animal cells only have a cell membrane.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The total magnification of a microscope with 10× eyepiece and 40× objective is:',
                        'options' => ['50×', '400×', '4×', '30×'],
                        'correct_answer' => '400×',
                        'explanation' => 'Total magnification = eyepiece × objective = 10 × 40 = 400×.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'Onion epidermal cells are often used to observe cell structure because they are thin and transparent.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Onion epidermis is a single layer of cells, making it ideal for light microscopy.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Iodine solution is used to stain onion cells because it:',
                        'options' => ['Stains the nucleus yellow', 'Stains the cell wall blue', 'Stains the nucleus brown/yellow', 'Makes cells green'],
                        'correct_answer' => 'Stains the nucleus brown/yellow',
                        'explanation' => 'Iodine stains the nucleus darker, making it easier to see against the lighter cytoplasm.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'The organelle responsible for photosynthesis in plant cells is:',
                        'options' => ['Mitochondria', 'Chloroplast', 'Nucleus', 'Vacuole'],
                        'correct_answer' => 'Chloroplast',
                        'explanation' => 'Chloroplasts contain chlorophyll and are the site of photosynthesis.',
                        'points' => 1,
                    ],
                ],
            ],

            // Food Tests
            [
                'experiment' => 'Food Tests - Starch, Sugars, Proteins, Lipids',
                'bank' => [
                    'title' => 'Food Tests Quiz',
                    'description' => 'Test your understanding of food nutrient tests and their results.',
                    'time_limit' => 12,
                    'passing_score' => 60,
                ],
                'questions' => [
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Which reagent is used to test for starch?',
                        'options' => ['Benedict\'s solution', 'Biuret reagent', 'Iodine solution', 'Sudan III'],
                        'correct_answer' => 'Iodine solution',
                        'explanation' => 'Iodine solution turns blue-black in the presence of starch.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'Benedict\'s test is used to detect:',
                        'options' => ['Starch', 'Reducing sugars', 'Proteins', 'Lipids'],
                        'correct_answer' => 'Reducing sugars',
                        'explanation' => 'Benedict\'s solution changes from blue to green/yellow/orange/brick-red when heated with reducing sugars.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'true_false',
                        'question_text' => 'The Biuret test turns purple in the presence of proteins.',
                        'options' => null,
                        'correct_answer' => 'True',
                        'explanation' => 'Biuret reagent (copper sulfate + sodium hydroxide) turns from blue to purple/violet with proteins.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'To test for lipids (fats and oils), you would use:',
                        'options' => ['Iodine solution', 'Benedict\'s solution', 'Sudan III or ethanol emulsion test', 'Biuret reagent'],
                        'correct_answer' => 'Sudan III or ethanol emulsion test',
                        'explanation' => 'Sudan III stains lipids red, or the ethanol emulsion test produces a white cloudy emulsion with fats.',
                        'points' => 1,
                    ],
                    [
                        'question_type' => 'mcq',
                        'question_text' => 'A positive Benedict\'s test showing brick-red color indicates:',
                        'options' => ['No sugar present', 'Low sugar concentration', 'High sugar concentration', 'Protein present'],
                        'correct_answer' => 'High sugar concentration',
                        'explanation' => 'The color progression is blue → green → yellow → orange → brick-red, with brick-red indicating the highest concentration.',
                        'points' => 1,
                    ],
                ],
            ],
        ];

        foreach ($assessments as $assessment) {
            // Find the experiment
            $experiment = $experiments->get($assessment['experiment']);
            
            if (!$experiment) {
                $this->command->warn("Experiment not found: {$assessment['experiment']}");
                continue;
            }

            // Create question bank
            $questionBank = VrQuestionBank::create([
                'vr_experiment_id' => $experiment->id,
                'title' => $assessment['bank']['title'],
                'description' => $assessment['bank']['description'],
                'time_limit' => $assessment['bank']['time_limit'],
                'passing_score' => $assessment['bank']['passing_score'],
                'is_active' => true,
                'display_order' => 1,
            ]);

            // Create questions
            foreach ($assessment['questions'] as $index => $question) {
                VrQuestion::create([
                    'question_bank_id' => $questionBank->id,
                    'question_type' => $question['question_type'],
                    'question_text' => $question['question_text'],
                    'options' => $question['options'],
                    'correct_answer' => $question['correct_answer'],
                    'explanation' => $question['explanation'],
                    'points' => $question['points'],
                    'order' => $index + 1,
                ]);
            }

            $this->command->info("Created assessment: {$assessment['bank']['title']} with " . count($assessment['questions']) . " questions");
        }
    }
}

