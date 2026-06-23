<?php

/**
 * One-off generator for PhET extension/remediation question bank JSON files.
 * Run: php database/seeders/scripts/generate_phet_extension_questions.php
 */

$basePath = dirname(__DIR__);
$dataFiles = glob($basePath.'/data/phet_*.json');

function loadExperiments(array $files): array
{
    $bySlug = [];
    foreach ($files as $file) {
        $entries = json_decode(file_get_contents($file), true);
        foreach ($entries as $entry) {
            $slug = $entry['phet_slug'] ?? null;
            if ($slug) {
                $bySlug[$slug] = $entry;
            }
        }
    }

    return $bySlug;
}

function mcq(int $order, string $text, array $options, string $correct, string $explanation): array
{
    return [
        'order' => $order,
        'question_type' => 'mcq',
        'question_text' => $text,
        'options' => $options,
        'correct_answer' => $correct,
        'explanation' => $explanation,
        'points' => 1,
    ];
}

function tf(int $order, string $text, string $correct, string $explanation): array
{
    return [
        'order' => $order,
        'question_type' => 'true_false',
        'question_text' => $text,
        'correct_answer' => $correct,
        'explanation' => $explanation,
        'points' => 1,
    ];
}

function sa(int $order, string $text, string $correct, string $explanation): array
{
    return [
        'order' => $order,
        'question_type' => 'short_answer',
        'question_text' => $text,
        'correct_answer' => $correct,
        'explanation' => $explanation,
        'points' => 1,
    ];
}

function makeEntry(array $exp, string $subject, string $description, array $questions): array
{
    return [
        'external_url' => $exp['external_url'],
        'phet_slug' => $exp['phet_slug'],
        'question_bank' => [
            'title' => $exp['name'].' — '.$subject.' Assessment',
            'description' => $description,
            'time_limit' => 15,
            'passing_score' => 60,
            'is_active' => false,
        ],
        'questions' => $questions,
    ];
}

$experiments = loadExperiments($dataFiles);

$questionSets = [
    'arithmetic' => [
        mcq(1, 'In Multiply mode, the missing value you complete is called the:', ['Factor', 'Sum', 'Quotient', 'Remainder'], 'Factor', 'A factor multiplies with another to give a product.'),
        mcq(2, 'Multiplication and division are:', ['Inverse operations', 'Unrelated operations', 'Only for decimals', 'Always equal'], 'Inverse operations', 'Division undoes multiplication for the same numbers.'),
        mcq(3, 'The area model in the sim helps visualise:', ['Multiplication as rows and columns', 'Chemical bonding', 'Wave speed', 'Orbital motion'], 'Multiplication as rows and columns', 'Rectangular grids represent products.'),
        mcq(4, 'In Factor mode, you decompose a number into:', ['Two factors that multiply to the product', 'Prime atoms', 'Wave frequencies', 'Orbital radii'], 'Two factors that multiply to the product', 'Factoring finds factor pairs.'),
        tf(5, 'Knowing multiplication facts fluently supports mental calculation.', 'True', 'Fluency reduces effort in multi-step problems.'),
        tf(6, 'Division always produces a whole number with no remainder.', 'False', 'Many divisions leave a remainder or give a decimal.'),
        mcq(7, 'A timed challenge in the sim mainly helps improve:', ['Speed and accuracy', 'Atomic number', 'Wave interference', 'Orbital eccentricity'], 'Speed and accuracy', 'Practice builds recall and confidence.'),
        mcq(8, 'If 7 × 8 = 56, then 56 ÷ 8 equals:', ['7', '8', '56', '64'], '7', 'Division reverses multiplication.'),
        sa(9, 'Name one mode available in the Arithmetic simulation.', 'multiply', 'Modes include Multiply, Factor, and Divide.'),
        sa(10, 'Write the relationship between dividend, divisor, and quotient.', 'dividend ÷ divisor = quotient', 'Division partitions a total into equal groups.'),
    ],
    'fractions-intro' => [
        mcq(1, 'In a fraction a/b, the top number is the:', ['Numerator', 'Denominator', 'Product', 'Quotient'], 'Numerator', 'The numerator counts parts taken.'),
        mcq(2, 'An improper fraction has a numerator that is:', ['Greater than or equal to the denominator', 'Always zero', 'Always one', 'Smaller than one only'], 'Greater than or equal to the denominator', 'Values like 5/4 exceed one whole.'),
        mcq(3, 'Equivalent fractions represent:', ['The same quantity', 'Different wholes only', 'Negative values only', 'Prime numbers'], 'The same quantity', 'Different numerators/denominators can name one amount.'),
        mcq(4, 'On the number line, 1/2 lies:', ['Halfway between 0 and 1', 'At 2', 'Beyond 2', 'Only at 0'], 'Halfway between 0 and 1', 'Unit fractions partition the interval from 0 to 1.'),
        tf(5, 'A mixed number combines a whole number and a proper fraction.', 'True', 'e.g. 2 1/3 means two wholes plus one third.'),
        tf(6, 'Fractions with different denominators can never be compared.', 'False', 'Common denominators or visual models allow comparison.'),
        mcq(7, 'The Lab screen is useful for:', ['Comparing two fractions side by side', 'Measuring voltage', 'Counting protons', 'Plotting orbits'], 'Comparing two fractions side by side', 'Visual comparison supports ordering.'),
        mcq(8, '3/4 of a whole means:', ['Three equal parts out of four', 'Four thirds', 'Three wholes', 'One quarter'], 'Three equal parts out of four', 'Denominator shows equal parts; numerator shows how many.'),
        sa(9, 'Convert 7/4 to a mixed number.', '1 3/4', '7 ÷ 4 = 1 remainder 3.'),
        sa(10, 'Name one representation used in Fractions: Intro besides symbols.', 'number line', 'Also fraction bars and area models.'),
    ],
    'fraction-matcher' => [
        mcq(1, 'Two fractions are equivalent when they:', ['Represent the same portion of a whole', 'Have identical numerators only', 'Always have denominator 10', 'Sum to 1'], 'Represent the same portion of a whole', 'Equivalence is about equal value.'),
        mcq(2, 'Simplifying 8/12 gives:', ['2/3', '4/6', '8/12', '3/2'], '2/3', 'Divide numerator and denominator by 4.'),
        mcq(3, 'To compare 2/3 and 3/5, a useful strategy is:', ['Convert to a common denominator', 'Add denominators', 'Multiply numerators only', 'Ignore denominators'], 'Convert to a common denominator', 'Common denominators make sizes comparable.'),
        mcq(4, 'The area model helps because it shows:', ['Fraction size visually', 'Electric field direction', 'Nuclear decay rate', 'Orbital period'], 'Fraction size visually', 'Shaded regions reveal relative magnitude.'),
        tf(5, '1/2 and 2/4 are equivalent fractions.', 'True', 'Multiply numerator and denominator of 1/2 by 2 to get 2/4.'),
        tf(6, 'A larger denominator always means a larger fraction.', 'False', 'Value depends on both numerator and denominator.'),
        mcq(7, 'Matching pairs correctly in the game requires checking:', ['Equivalence, not just appearance', 'Atomic mass only', 'Wave phase only', 'Battery polarity only'], 'Equivalence, not just appearance', 'Different shapes may show equal fractions.'),
        mcq(8, '6/9 in lowest terms is:', ['2/3', '3/2', '6/9', '1/9'], '2/3', 'Divide top and bottom by 3.'),
        sa(9, 'Give one pair of equivalent fractions for 1/3.', '2/6', 'Multiply numerator and denominator by the same number.'),
        sa(10, 'What operation finds a common denominator for 1/4 and 1/3?', 'multiply', 'Use LCM of denominators or cross-multiply to compare.'),
    ],
    'fractions-equality' => [
        mcq(1, 'Multiplying numerator and denominator by the same nonzero number:', ['Produces an equivalent fraction', 'Changes the value', 'Always gives 1', 'Is undefined'], 'Produces an equivalent fraction', 'Scaling both parts preserves value.'),
        mcq(2, '4/6 simplified to lowest terms is:', ['2/3', '3/2', '1/6', '4/6'], '2/3', 'Divide numerator and denominator by 2.'),
        mcq(3, 'Equivalent fractions are useful when:', ['Adding fractions with unlike denominators', 'Measuring capacitance', 'Calculating half-life', 'Finding orbital speed'], 'Adding fractions with unlike denominators', 'Common denominators enable addition.'),
        mcq(4, 'The bar model shows equality when:', ['Both bars show the same shaded amount', 'Denominators match only', 'Numerators are both even', 'Bars are different colours'], 'Both bars show the same shaded amount', 'Visual equality confirms symbolic equivalence.'),
        tf(5, 'Dividing numerator and denominator by the same number simplifies a fraction.', 'True', 'This preserves value while reducing terms.'),
        tf(6, '3/4 and 6/8 represent different quantities.', 'False', '6/8 simplifies to 3/4.'),
        mcq(7, 'In challenge mode with one fraction hidden, you use:', ['Equivalence reasoning', 'Nuclear equations', 'Kirchhoff laws', 'Beer-Lambert law'], 'Equivalence reasoning', 'Deduce the missing equivalent fraction.'),
        mcq(8, '2/5 and 4/10 are:', ['Equivalent', 'Not comparable', 'Always improper', 'Unit fractions only'], 'Equivalent', 'Multiply 2/5 by 2/2 to get 4/10.'),
        sa(9, 'Write 3/4 as an equivalent fraction with denominator 12.', '9/12', 'Multiply numerator and denominator by 3.'),
        sa(10, 'State what stays the same when forming equivalent fractions.', 'value', 'The quantity represented does not change.'),
    ],
    'area-model-introduction' => [
        mcq(1, 'The area of a rectangle equals:', ['Length × width', 'Length + width', 'Length ÷ width', 'Width − length'], 'Length × width', 'Area counts unit squares covering the shape.'),
        mcq(2, 'A partitioned rectangle helps show:', ['The distributive property', 'Alpha decay', 'Photon energy only', 'Orbital resonance'], 'The distributive property', 'Sub-rectangles add to total area.'),
        mcq(3, 'Area is measured in:', ['Square units', 'Linear units only', 'Cubic units only', 'Newtons'], 'Square units', 'Area is two-dimensional measure.'),
        mcq(4, 'Breaking a large multiplication into parts uses:', ['Partial products', 'Half-life', 'Work function', 'Redshift only'], 'Partial products', 'Each sub-area contributes to the product.'),
        tf(5, 'Multiplication can be interpreted as filling a rectangle with unit squares.', 'True', 'Rows × columns gives total square count.'),
        tf(6, 'A rectangle with zero width can have positive area.', 'False', 'Zero width gives zero area.'),
        mcq(7, 'In Game mode you practise:', ['Calculating areas of partitioned rectangles', 'Balancing nuclear equations', 'Measuring absorbance', 'Tracing field lines'], 'Calculating areas of partitioned rectangles', 'Game mode reinforces area reasoning.'),
        mcq(8, 'A 3 by 4 rectangle has area:', ['12 square units', '7 square units', '1 square unit', '34 square units'], '12 square units', '3 × 4 = 12.'),
        sa(9, 'Write the formula for the area of a rectangle.', 'A = l × w', 'Length times width gives area.'),
        sa(10, 'Name one way the sim partitions a rectangle.', 'partial products', 'Splits into smaller rectangles for easier multiplication.'),
    ],
    'calculus-grapher' => [
        mcq(1, 'The derivative at a point represents:', ['The gradient of the tangent', 'The area under the curve', 'The y-intercept only', 'The average value'], 'The gradient of the tangent', 'Derivative is instantaneous rate of change.'),
        mcq(2, 'Where the derivative is zero, the original function often has:', ['A turning point', 'Maximum area only', 'Infinite slope', 'No x-values'], 'A turning point', 'Zero gradient indicates horizontal tangent.'),
        mcq(3, 'A positive derivative means the function is:', ['Increasing', 'Decreasing', 'Constant', 'Undefined'], 'Increasing', 'Positive slope rises left to right.'),
        mcq(4, 'The integral curve accumulates:', ['Area under the original function', 'Charge on plates', 'Decay constant', 'Orbital eccentricity'], 'Area under the original function', 'Integration sums infinitesimal areas.'),
        tf(5, 'Differentiation and integration are inverse operations.', 'True', 'This is the Fundamental Theorem of Calculus.'),
        tf(6, 'The derivative of a constant function is always 1.', 'False', 'Derivative of a constant is zero.'),
        mcq(7, 'Tracing left to right on the integral curve shows:', ['Accumulated area growing', 'Mass defect', 'Emission lines', 'Half-life decreasing'], 'Accumulated area growing', 'Area builds as x increases.'),
        mcq(8, 'For y = x² near x = 0, the derivative is:', ['Near zero', 'Always negative', 'Undefined', 'Equal to x²'], 'Near zero', 'Gradient at the vertex is zero.'),
        sa(9, 'State what a turning point indicates about the derivative.', 'derivative equals zero', 'Horizontal tangent at maxima or minima.'),
        sa(10, 'Name one preset function in Calculus Grapher.', 'sine', 'Also parabola, step, and custom drawn curves.'),
    ],
    'fourier-making-waves' => [
        mcq(1, 'A complex periodic wave can be built from:', ['Sinusoidal harmonics', 'Only square pulses', 'Random noise only', 'Static charges only'], 'Sinusoidal harmonics', 'Fourier synthesis sums sine/cosine terms.'),
        mcq(2, 'Increasing the amplitude of a harmonic changes:', ['Its contribution to the resultant wave', 'The speed of light', 'Atomic number', 'Capacitance only'], 'Its contribution to the resultant wave', 'Larger amplitude means stronger harmonic presence.'),
        mcq(3, 'A square wave contains:', ['Many odd harmonics', 'No harmonics', 'Only DC component', 'Only gamma rays'], 'Many odd harmonics', 'Sharp edges require high-frequency components.'),
        mcq(4, 'Harmonic content relates to:', ['Timbre of musical sounds', 'Nuclear binding energy', 'pH of solution', 'Cell membrane thickness only'], 'Timbre of musical sounds', 'Different instruments have different harmonic mixes.'),
        tf(5, 'Any periodic waveform can be decomposed into sinusoids.', 'True', 'This is the core idea of Fourier series.'),
        tf(6, 'Adding harmonics always makes a wave perfectly sinusoidal.', 'False', 'More harmonics can approximate complex shapes.'),
        mcq(7, 'In Wave Game you match a target by:', ['Choosing harmonic amplitudes', 'Balancing chemical equations', 'Setting slit width', 'Adjusting pH'], 'Choosing harmonic amplitudes', 'Select components to reproduce the target shape.'),
        mcq(8, 'Higher-frequency harmonics affect:', ['Fine detail and sharp features', 'Gravitational mass only', 'Half-life only', 'Plate separation only'], 'Fine detail and sharp features', 'High frequencies capture rapid changes.'),
        sa(9, 'What is a Fourier series a sum of?', 'sinusoids', 'Sines and cosines at harmonic frequencies.'),
        sa(10, 'Name one waveform you can approximate in the sim.', 'square wave', 'Also sawtooth and triangle waves.'),
    ],
    'alpha-decay' => [
        mcq(1, 'An alpha particle consists of:', ['2 protons and 2 neutrons', '1 electron', '1 proton only', 'A photon'], '2 protons and 2 neutrons', 'Alpha particle is a helium-4 nucleus.'),
        mcq(2, 'In alpha decay, the mass number decreases by:', ['4', '1', '0', '2 only'], '4', 'Alpha emission removes four nucleons.'),
        mcq(3, 'Half-life is the time for:', ['Half the nuclei to decay', 'All nuclei to decay', 'Alpha speed to double', 'Charge to reverse'], 'Half the nuclei to decay', 'Statistical half of a large sample decays in one half-life.'),
        mcq(4, 'Alpha radiation has:', ['Low penetrating power', 'Highest penetrating power', 'No charge', 'Infinite range in air'], 'Low penetrating power', 'Stopped by paper or skin.'),
        tf(5, 'Radioactive decay of a single nucleus is random.', 'True', 'Individual decay times cannot be predicted.'),
        tf(6, 'Alpha decay increases the atomic number by 2.', 'False', 'Atomic number decreases by 2 when helium nucleus is lost.'),
        mcq(7, 'Adding more atoms in the sim shows decay is:', ['Statistical over many nuclei', 'Deterministic per atom', 'Instant for all', 'Unrelated to half-life'], 'Statistical over many nuclei', 'Large samples follow predictable averages.'),
        mcq(8, 'The daughter nucleus after alpha emission has atomic number reduced by:', ['2', '4', '0', '1'], '2', 'Two protons are removed with the alpha particle.'),
        sa(9, 'Write the symbol for an alpha particle.', 'He-4', 'Also written as ⁴₂He or α.'),
        sa(10, 'Define half-life in one phrase.', 'time for half the nuclei to decay', 'Characteristic decay time for a radioisotope.'),
    ],
    'beta-decay' => [
        mcq(1, 'In beta-minus decay, a neutron becomes:', ['A proton, electron, and antineutrino', 'An alpha particle', 'Two protons', 'A photon only'], 'A proton, electron, and antineutrino', 'Charge is conserved via electron emission.'),
        mcq(2, 'Beta decay changes the atomic number by:', ['±1', '±4', '0 always', '±2 always'], '±1', 'One proton gained or lost relative to beta type.'),
        mcq(3, 'The mass number during beta-minus decay:', ['Stays the same', 'Decreases by 4', 'Increases by 4', 'Doubles'], 'Stays the same', 'Nucleon count is unchanged.'),
        mcq(4, 'Carbon-14 dating relies on:', ['Beta decay of carbon-14', 'Alpha decay of uranium', 'Gamma emission only', 'Nuclear fission'], 'Beta decay of carbon-14', 'C-14 decays to N-14 with known half-life.'),
        tf(5, 'Beta particles are high-speed electrons (or positrons).', 'True', 'Beta radiation is charged lepton emission.'),
        tf(6, 'Beta radiation penetrates less than alpha radiation.', 'False', 'Beta penetrates further than alpha; gamma penetrates most.'),
        mcq(7, 'Balanced nuclear equations require conserving:', ['Nucleon number and charge', 'Colour only', 'Orbital eccentricity', 'Slit width'], 'Nucleon number and charge', 'Total protons and nucleons must balance.'),
        mcq(8, 'Medical tracers may use beta emitters because:', ['Detectable radiation with useful half-life', 'No radiation is emitted', 'They never decay', 'They only emit visible light'], 'Detectable radiation with useful half-life', 'Short-lived isotopes image metabolic processes.'),
        sa(9, 'Name the particle emitted in beta-minus decay.', 'electron', 'Also called a beta particle.'),
        sa(10, 'What happens to the atomic number in beta-minus decay?', 'increases by 1', 'Neutron converts to proton in the nucleus.'),
    ],
    'blackbody-spectrum' => [
        mcq(1, 'As temperature increases, the peak wavelength:', ['Shifts to shorter wavelengths', 'Shifts to longer wavelengths', 'Stays fixed', 'Disappears'], 'Shifts to shorter wavelengths', "Wien's law: hotter peaks at shorter λ."),
        mcq(2, 'A blackbody spectrum shows emission at:', ['All wavelengths with a characteristic peak', 'One wavelength only', 'No infrared', 'Only radio waves'], 'All wavelengths with a characteristic peak', 'Thermal bodies emit a broad continuum.'),
        mcq(3, 'Hot metal glowing red then white illustrates:', ['Rising temperature shifting peak emission', 'Alpha decay', 'Beta conversion', 'Capacitor charging'], 'Rising temperature shifting peak emission', 'Colour change reflects peak moving through visible band.'),
        mcq(4, "Wien's Law relates peak wavelength to:", ['Temperature', 'Mass only', 'Charge only', 'Half-life'], 'Temperature', 'Higher T means shorter peak wavelength.'),
        tf(5, 'All objects above absolute zero emit electromagnetic radiation.', 'True', 'Thermal radiation is universal.'),
        tf(6, 'Cooler stars always peak at shorter wavelengths than hotter stars.', 'False', 'Hotter stars peak at shorter wavelengths.'),
        mcq(7, 'The visible band overlay helps compare:', ['Which part of spectrum we see', 'Nuclear binding energy', 'Fraction equivalence', 'Orbital period only'], 'Which part of spectrum we see', 'Relates thermal peak to perceived colour.'),
        mcq(8, 'Comparing the Sun to a light bulb shows:', ['Different peak wavelengths at different temperatures', 'Identical spectra always', 'No infrared emission', 'Only gamma rays'], 'Different peak wavelengths at different temperatures', 'Temperature determines spectral shape.'),
        sa(9, 'State how colour of a hot object changes as temperature rises.', 'shifts from red to white', 'Peak moves through visible spectrum.'),
        sa(10, 'What does the height of the spectrum curve represent?', 'intensity', 'Power emitted per wavelength interval.'),
    ],
    'capacitor-lab-basics' => [
        mcq(1, 'Capacitance measures the ability to:', ['Store charge per volt', 'Create nuclear fusion', 'Emit alpha particles', 'Split atoms'], 'Store charge per volt', 'C = Q/V relates charge and potential difference.'),
        mcq(2, 'Increasing plate separation generally:', ['Decreases capacitance', 'Increases capacitance', 'Has no effect', 'Stops all charge'], 'Decreases capacitance', 'Larger gap reduces stored charge at same voltage.'),
        mcq(3, 'A dielectric between plates:', ['Increases capacitance', 'Removes all field', 'Prevents voltage', 'Eliminates charge'], 'Increases capacitance', 'Dielectric polarisation stores more charge.'),
        mcq(4, 'Energy stored in a capacitor depends on:', ['Voltage and capacitance', 'Mass only', 'Half-life', 'Orbital radius only'], 'Voltage and capacitance', 'E = ½CV² for stored energy.'),
        tf(5, 'Capacitors store energy in the electric field between plates.', 'True', 'Field energy is released when discharged.'),
        tf(6, 'Capacitance has units of ohms.', 'False', 'Capacitance is measured in farads (F).'),
        mcq(7, 'Connecting a battery charges the plates with:', ['Equal and opposite charge', 'Same sign on both plates', 'No field', 'Only neutrons'], 'Equal and opposite charge', 'Separation of charge creates potential difference.'),
        mcq(8, 'Larger plate area typically:', ['Increases capacitance', 'Decreases capacitance', 'Stops charging', 'Doubles mass'], 'Increases capacitance', 'More area allows more charge storage.'),
        sa(9, 'Write the symbol for capacitance.', 'C', 'Measured in farads.'),
        sa(10, 'Name one factor you can change in Capacitor Lab: Basics.', 'plate separation', 'Also plate area and dielectric material.'),
    ],
    'circuit-construction-kit-ac' => [
        mcq(1, 'In AC circuits, current:', ['Reverses direction periodically', 'Flows one way only', 'Is always zero', 'Equals DC always'], 'Reverses direction periodically', 'Alternating current oscillates in direction.'),
        mcq(2, 'A capacitor in AC allows:', ['Current to flow as plates charge/discharge', 'Steady DC with no effect', 'Nuclear decay', 'Alpha emission'], 'Current to flow as plates charge/discharge', 'Changing voltage moves charge on/off plates.'),
        mcq(3, 'An inductor opposes changes in:', ['Current', 'Mass', 'Atomic number', 'Half-life'], 'Current', 'Induced emf resists ΔI (Lenz law).'),
        mcq(4, 'RMS voltage relates to AC as:', ['Effective equivalent DC value', 'Peak value always', 'Zero always', 'Frequency only'], 'Effective equivalent DC value', 'RMS gives same power as equivalent DC.'),
        tf(5, 'Household mains electricity is typically AC.', 'True', 'AC is used for efficient transmission and transformers.'),
        tf(6, 'AC frequency has no effect on bulb brightness in resistive circuits.', 'False', 'Reactive components make response frequency-dependent.'),
        mcq(7, 'Oscilloscope view shows:', ['Voltage varying with time', 'Orbital paths', 'Emission spectra', 'Fraction bars only'], 'Voltage varying with time', 'Waveform displays periodic AC signal.'),
        mcq(8, 'Adding a capacitor in series with a resistor creates:', ['Phase shift between voltage and current', 'Permanent magnetism', 'Nuclear fission', 'Alpha particles'], 'Phase shift between voltage and current', 'Capacitive reactance causes phase difference.'),
        sa(9, 'What does AC stand for?', 'alternating current', 'Current direction alternates periodically.'),
        sa(10, 'Name one AC component in the kit besides resistors.', 'capacitor', 'Also inductors and AC voltage source.'),
    ],
    'hydrogen-atom' => [
        mcq(1, 'In the Bohr model, electrons occupy:', ['Quantised energy levels', 'Any energy continuously', 'The nucleus', 'Only the ground state forever'], 'Quantised energy levels', 'Only certain orbits are allowed.'),
        mcq(2, 'A photon is emitted when an electron:', ['Falls to a lower energy level', 'Rises to higher level', 'Leaves the atom without transition', 'Stops moving'], 'Falls to a lower energy level', 'Energy difference is released as photon.'),
        mcq(3, 'The Lyman series involves transitions to:', ['n = 1', 'n = 2', 'n = 3', 'n = ∞ only'], 'n = 1', 'Ultraviolet lines end at ground state.'),
        mcq(4, 'Hydrogen spectral lines are:', ['Discrete wavelengths', 'A continuous rainbow only', 'Identical for all elements', 'Unrelated to energy levels'], 'Discrete wavelengths', 'Each line matches a specific transition.'),
        tf(5, 'Absorption occurs when a photon raises an electron to a higher level.', 'True', 'Matching photon energy excites the electron.'),
        tf(6, 'All elements have the same hydrogen-like spectrum.', 'False', 'Each element has unique energy level spacings.'),
        mcq(7, 'Higher n levels in hydrogen are:', ['Closer in energy (more crowded)', 'Equally spaced always', 'Below ground state', 'Unobservable'], 'Closer in energy (more crowded)', 'Energy spacing decreases at large n.'),
        mcq(8, 'The visible Balmer series ends at n =:', ['2', '1', '3', '4'], '2', 'Transitions to n=2 produce visible lines.'),
        sa(9, 'Name one hydrogen spectral series.', 'Balmer', 'Also Lyman, Paschen, etc.'),
        sa(10, 'What is emitted during an electron transition down?', 'photon', 'Photon energy equals level difference.'),
    ],
    'keplers-laws' => [
        mcq(1, "Kepler's first law states orbits are:", ['Ellipses with the Sun at one focus', 'Perfect circles only', 'Straight lines', 'Random curves'], 'Ellipses with the Sun at one focus', 'Planets move on elliptical paths.'),
        mcq(2, 'A planet moves fastest when it is:', ['Closest to the Sun (perihelion)', 'Farthest from the Sun', 'At aphelion only', 'Stationary'], 'Closest to the Sun (perihelion)', 'Angular momentum conservation speeds near perihelion.'),
        mcq(3, "Kepler's third law relates orbital period to:", ['Semi-major axis', 'Planet colour', 'Atmospheric pressure', 'Nuclear charge'], 'Semi-major axis', 'T² ∝ a³ for solar system bodies.'),
        mcq(4, 'Eccentricity measures:', ['How stretched an ellipse is', 'Planet mass only', 'Surface temperature', 'Half-life'], 'How stretched an ellipse is', 'e=0 is circle; higher e is more elongated.'),
        tf(5, 'Planets sweep equal areas in equal times.', 'True', "This is Kepler's second law."),
        tf(6, 'All planetary orbits have the same period.', 'False', 'Period depends on orbital size.'),
        mcq(7, 'Increasing semi-major axis generally:', ['Increases orbital period', 'Decreases period', 'Stops motion', 'Removes gravity'], 'Increases orbital period', 'Larger orbits take longer to complete.'),
        mcq(8, 'The Sun sits at:', ['One focus of the ellipse', 'The centre of the circle always', 'Aphelion', 'Outside the orbit'], 'One focus of the ellipse', 'Other focus is empty for planetary orbits.'),
        sa(9, "State Kepler's third law in words.", 'period squared proportional to semi-major axis cubed', 'T² ∝ a³.'),
        sa(10, 'Name the point of closest approach to the Sun.', 'perihelion', 'Farthest point is aphelion.'),
    ],
    'models-of-the-hydrogen-atom' => [
        mcq(1, 'The Bohr model introduced:', ['Quantised electron orbits', 'Continuous energy only', 'Nuclear fission', 'AC circuits'], 'Quantised electron orbits', 'Electrons occupy discrete radii.'),
        mcq(2, 'The quantum model describes electrons as:', ['Probability clouds (orbitals)', 'Fixed planet-like paths only', 'Alpha particles', 'Neutrons in shells'], 'Probability clouds (orbitals)', 'Orbitals show likely electron locations.'),
        mcq(3, 'Classical model failure included:', ['Orbiting electron should radiate and collapse', 'Too many spectral lines', 'No protons', 'Infinite mass'], 'Orbiting electron should radiate and collapse', 'Accelerating charge radiates energy classically.'),
        mcq(4, 'Spectral lines support:', ['Quantised energy levels', 'Continuous energy only', 'No photons', 'Random colours only'], 'Quantised energy levels', 'Discrete transitions produce line spectra.'),
        tf(5, 'Later models improved on Bohr by including wave nature of electrons.', 'True', 'Quantum mechanics replaced fixed orbits with orbitals.'),
        tf(6, 'All atomic models predict identical spectra.', 'False', 'Only quantum models match hydrogen spectrum quantitatively.'),
        mcq(7, 'Switching models in the sim helps compare:', ['Historical explanations of hydrogen', 'Cooking recipes', 'Fraction equivalence', 'pH curves only'], 'Historical explanations of hydrogen', 'Tracks development of atomic theory.'),
        mcq(8, 'Ground state is the:', ['Lowest energy level', 'Highest energy level', 'Ionised atom', 'Nucleus only'], 'Lowest energy level', 'Electrons normally occupy lowest available level.'),
        sa(9, 'Name one historical model shown in the sim.', 'Bohr', 'Also classical/planetary and quantum models.'),
        sa(10, 'What problem did quantisation solve for hydrogen?', 'stable discrete orbits', 'Prevented classical spiral into nucleus.'),
    ],
    'neon-lights-and-other-discharge-lamps' => [
        mcq(1, 'Discharge lamps produce light when:', ['Electrons excite gas atoms which then emit photons', 'Metal melts', 'Capacitors discharge only', 'Alpha particles absorb light'], 'Electrons excite gas atoms which then emit photons', 'De-excitation releases photons of specific energies.'),
        mcq(2, 'Emission spectra of gases are:', ['Discrete lines', 'Continuous rainbows only', 'Identical for all gases', 'Invisible'], 'Discrete lines', 'Each transition gives a specific wavelength.'),
        mcq(3, "Each element's emission spectrum acts as:", ['A unique fingerprint', 'A random pattern', 'Always one green line', 'Unrelated to electrons'], 'A unique fingerprint', 'Line positions identify the element.'),
        mcq(4, 'A photon colour corresponds to:', ['Energy of the electron transition', 'Nuclear mass only', 'Orbital period', 'Plate area'], 'Energy of the electron transition', 'E = hf links photon energy to colour.'),
        tf(5, 'Neon signs use gas discharge to produce coloured light.', 'True', 'Different gases give different colours.'),
        tf(6, 'All spectral lines from one gas have the same wavelength.', 'False', 'Multiple transitions produce multiple lines.'),
        mcq(7, 'The spectrometer in the sim displays:', ['Wavelengths of emitted lines', 'Gravitational force', 'Fraction numerators', 'Capacitance only'], 'Wavelengths of emitted lines', 'Spectrometer disperses light by wavelength.'),
        mcq(8, 'Colliding electrons in the tube:', ['Excite atoms to higher energy levels', 'Remove all protons', 'Stop all current', 'Create permanent magnets'], 'Excite atoms to higher energy levels', 'Excitation followed by emission produces light.'),
        sa(9, 'Name one gas used in the discharge lamp sim.', 'neon', 'Also hydrogen and other gases.'),
        sa(10, 'What is observed when electrons return to lower levels?', 'photon emission', 'Light released with energy equal to level gap.'),
    ],
    'nuclear-fission' => [
        mcq(1, 'Nuclear fission splits:', ['A heavy nucleus into smaller fragments', 'An electron from an atom', 'Light into photons only', 'A capacitor plate'], 'A heavy nucleus into smaller fragments', 'Heavy nuclei divide releasing energy.'),
        mcq(2, 'A chain reaction requires:', ['Released neutrons to trigger more fissions', 'Only one neutron total', 'No neutrons', 'Alpha particles only'], 'Released neutrons to trigger more fissions', 'Neutrons sustain repeated fission events.'),
        mcq(3, 'U-235 is fissile because:', ['Slow neutrons can cause fission', 'It never absorbs neutrons', 'It is the most abundant isotope', 'It emits only gamma rays'], 'Slow neutrons can cause fission', 'Thermal neutrons trigger U-235 splitting.'),
        mcq(4, 'Energy release in fission comes from:', ['Mass defect converted to energy', 'Chemical bond breaking', 'Friction only', 'Capacitor discharge'], 'Mass defect converted to energy', 'E = mc² relates lost mass to energy.'),
        tf(5, 'Moderators slow neutrons to improve fission probability.', 'True', 'Slow neutrons are more likely to be captured by U-235.'),
        tf(6, 'Fission increases total nucleon number.', 'False', 'Nucleons are redistributed; total conserved.'),
        mcq(7, 'Control rods in reactors:', ['Absorb neutrons to regulate reaction rate', 'Speed up all neutrons', 'Create alpha decay', 'Store charge'], 'Absorb neutrons to regulate reaction rate', 'Cadmium/boron rods limit chain reaction.'),
        mcq(8, 'Compared to U-238, U-235:', ['Sustains chain reactions more readily', 'Never fissions', 'Has fewer protons', 'Is not radioactive'], 'Sustains chain reactions more readily', 'U-238 needs fast neutrons for fission.'),
        sa(9, 'Name one product of uranium fission besides neutrons.', 'smaller nuclei', 'Fission fragments are lighter nuclei.'),
        sa(10, "Write Einstein's equation relating mass and energy.", 'E = mc²', 'Mass defect releases energy in fission.'),
    ],
    'quantum-measurement' => [
        mcq(1, 'Quantum measurement outcomes are:', ['Probabilistic', 'Always predictable for single trials', 'Identical every time', 'Unrelated to state'], 'Probabilistic', 'Born rule gives probabilities, not certainties.'),
        mcq(2, 'Superposition means a system is:', ['In a combination of states until measured', 'Always in one definite state', 'Classical only', 'Unmeasurable'], 'In a combination of states until measured', 'State is a mix of possibilities before measurement.'),
        mcq(3, 'Measurement collapses the state to:', ['A definite eigenstate', 'All states simultaneously', 'Zero energy', 'Classical orbit'], 'A definite eigenstate', 'Wave function collapse yields one outcome.'),
        mcq(4, 'Repeating many measurements reveals:', ['Statistical distribution of outcomes', 'Exact path of particle', 'No pattern', 'Only classical physics'], 'Statistical distribution of outcomes', 'Frequencies match predicted probabilities.'),
        tf(5, 'The Born rule links probability to amplitude squared.', 'True', 'Probability ∝ |ψ|².'),
        tf(6, 'Quantum measurement is irrelevant to quantum computing.', 'False', 'Measurement is central to readout in quantum computers.'),
        mcq(7, 'Changing detector angle in the sim changes:', ['Measurement probabilities', 'Gravitational constant', 'Half-life', 'Capacitance'], 'Measurement probabilities', 'Projection depends on measurement basis.'),
        mcq(8, 'Spin measurement along different axes:', ['May not have definite values simultaneously', 'Always gives 0', 'Violates charge conservation', 'Stops all rotation'], 'May not have definite values simultaneously', 'Non-commuting observables have uncertainty.'),
        sa(9, 'What happens to superposition after measurement?', 'collapse', 'System ends in a definite measured state.'),
        sa(10, 'Name one quantity measured in the spin experiment.', 'spin', 'Spin component along chosen axis.'),
    ],
    'quantum-wave-interference' => [
        mcq(1, 'The double-slit experiment demonstrates:', ['Wave-particle duality', 'Only particle behaviour', 'Only classical waves in water', 'Nuclear fission'], 'Wave-particle duality', 'Single quanta build interference over time.'),
        mcq(2, 'Closing one slit changes the pattern to:', ['Single-slit diffraction', 'No pattern at all', 'Alpha decay', 'Emission spectrum'], 'Single-slit diffraction', 'One opening produces diffraction not two-source interference.'),
        mcq(3, 'Which-path detection typically:', ['Destroys interference', 'Increases fringe contrast', 'Has no effect', 'Doubles wavelength'], 'Destroys interference', 'Which-path information collapses superposition.'),
        mcq(4, 'Fringe spacing depends on:', ['Wavelength and slit separation', 'Mass of Earth only', 'pH', 'Capacitance only'], 'Wavelength and slit separation', 'Longer λ or smaller separation widens fringes.'),
        tf(5, 'Interference builds up from many single-particle detections.', 'True', 'Pattern emerges statistically over time.'),
        tf(6, 'Quantum particles always follow a single classical path through both slits.', 'False', 'Superposition over paths explains interference.'),
        mcq(7, 'Narrower slits generally produce:', ['Wider diffraction patterns', 'No diffraction', 'Shorter wavelength', 'Higher atomic number'], 'Wider diffraction patterns', 'Diffraction spreads more for smaller apertures.'),
        mcq(8, 'Observation in quantum mechanics can:', ['Alter the measured system', 'Never affect outcomes', 'Only affect macro objects', 'Eliminate all photons'], 'Alter the measured system', 'Measurement interaction disturbs the state.'),
        sa(9, 'Name the pattern from two slits.', 'interference', 'Alternating bright and dark fringes.'),
        sa(10, 'What concept explains loss of fringes when which-path is known?', 'wave function collapse', 'Which-path information removes superposition.'),
    ],
    'atomic-spectroscopy' => [
        mcq(1, "Each element's emission spectrum is:", ['Unique like a fingerprint', 'Identical for all elements', 'Always continuous', 'Unrelated to electrons'], 'Unique like a fingerprint', 'Line positions identify elements.'),
        mcq(2, 'A spectral line wavelength relates to:', ['Energy difference between levels', 'Nuclear mass only', 'Orbital period', 'Plate separation'], 'Energy difference between levels', 'ΔE = hf connects transition energy to photon.'),
        mcq(3, 'Emission spectra are produced when electrons:', ['Fall to lower energy levels', 'Always absorb photons', 'Leave nucleus', 'Stop moving'], 'Fall to lower energy levels', 'De-excitation releases photons.'),
        mcq(4, 'Absorption spectra show:', ['Dark lines where light was absorbed', 'Only continuous colour', 'No relation to emission', 'Alpha particles'], 'Dark lines where light was absorbed', 'Missing wavelengths match emission lines.'),
        tf(5, 'Astronomers use spectra to identify elements in stars.', 'True', 'Stellar spectra reveal chemical composition.'),
        tf(6, 'All spectral lines of hydrogen have the same energy.', 'False', 'Different transitions have different energies.'),
        mcq(7, 'The spectrometer separates light by:', ['Wavelength', 'Mass only', 'Charge only', 'Half-life'], 'Wavelength', 'Dispersion maps wavelength to position.'),
        mcq(8, 'Photon energy increases when wavelength:', ['Decreases', 'Increases', 'Stays same', 'Doubles'], 'Decreases', 'E = hc/λ: shorter λ means higher energy.'),
        sa(9, 'Name one element selectable in Atomic Spectroscopy.', 'hydrogen', 'Also helium, mercury, etc.'),
        sa(10, 'What instrument records spectral line wavelengths?', 'spectrometer', 'Spectrometer disperses and measures λ.'),
    ],
    'beers-law-lab' => [
        mcq(1, 'Beer-Lambert Law states absorbance is proportional to:', ['Concentration', 'Temperature only', 'Mass of nucleus', 'Orbital speed'], 'Concentration', 'A ∝ c for fixed path length and wavelength.'),
        mcq(2, 'Higher concentration generally gives:', ['Higher absorbance', 'Lower absorbance', 'No change', 'Zero transmittance always'], 'Higher absorbance', 'More solute absorbs more light.'),
        mcq(3, 'Transmittance is the fraction of light that:', ['Passes through the sample', 'Is absorbed only', 'Causes fission', 'Creates interference'], 'Passes through the sample', 'Complement of absorbed/transmitted fraction.'),
        mcq(4, 'A calibration curve plots absorbance against:', ['Known concentrations', 'Time only', 'Orbital radius', 'Atomic number'], 'Known concentrations', 'Used to find unknown concentration.'),
        tf(5, 'A spectrophotometer measures light absorption by a solution.', 'True', 'Detector compares incident and transmitted intensity.'),
        tf(6, 'Solution colour is unrelated to wavelengths absorbed.', 'False', 'Complementary colour is what is transmitted.'),
        mcq(7, 'Changing wavelength affects absorbance because:', ['Different wavelengths are absorbed differently', 'Concentration changes', 'Cuvette vanishes', 'Law no longer applies'], 'Different wavelengths are absorbed differently', 'Absorption spectrum is wavelength-dependent.'),
        mcq(8, 'To find unknown concentration you use:', ['The calibration curve', 'Kepler\'s laws', 'Alpha decay data', 'Fourier harmonics'], 'The calibration curve', 'Interpolate absorbance on best-fit line.'),
        sa(9, 'State the proportionality in Beer-Lambert Law.', 'absorbance proportional to concentration', 'Also depends on path length and absorptivity.'),
        sa(10, 'Name one quantity the sim lets you adjust besides concentration.', 'wavelength', 'Also cuvette path and solution type.'),
    ],
    'charges-and-fields' => [
        mcq(1, 'Electric field lines point:', ['Away from positive and toward negative', 'Away from negative only', 'Randomly', 'Parallel to equipotentials'], 'Away from positive and toward negative', 'Field direction is force on positive test charge.'),
        mcq(2, 'Like charges placed near each other:', ['Repel', 'Attract', 'Have no interaction', 'Merge'], 'Repel', 'Same sign charges repel.'),
        mcq(3, 'Equipotential lines cross field lines:', ['Perpendicularly', 'Parallel always', 'Never relate', 'At random angles only'], 'Perpendicularly', 'No work along equipotential; field does work across.'),
        mcq(4, 'Field line density indicates:', ['Field strength', 'Mass', 'Half-life', 'Wavelength'], 'Field strength', 'Closer lines mean stronger field.'),
        tf(5, 'Electric fields can explain ion movement across cell membranes.', 'True', 'Membrane potentials drive ion flow in biology.'),
        tf(6, 'Negative charges create field lines pointing toward the charge.', 'True', 'Field converges on negative sources.'),
        mcq(7, 'Superposition means multiple charges:', ['Combine their fields vectorially', 'Cancel all fields', 'Remove voltage', 'Stop diffusion'], 'Combine their fields vectorially', 'Net field is sum of individual contributions.'),
        mcq(8, 'A test positive charge moves:', ['Along field direction', 'Opposite field always', 'Perpendicular only', 'With no force'], 'Along field direction', 'Force F = qE aligns with E for positive q.'),
        sa(9, 'What do equipotential lines connect?', 'points of equal voltage', 'Same potential; no work to move along line.'),
        sa(10, 'Name one biological system linked to electric fields in the learning outcomes.', 'nerve cell membranes', 'Ion gradients and membrane potentials.'),
    ],
    'my-solar-system' => [
        mcq(1, 'Orbital shape depends on:', ['Initial velocity and gravitational pull', 'Colour of planet', 'Chemical formula', 'pH'], 'Initial velocity and gravitational pull', 'Energy and angular momentum set orbit type.'),
        mcq(2, 'A circular orbit occurs when speed is:', ['Just right for the gravitational pull', 'Zero', 'Infinite', 'Unrelated to mass'], 'Just right for the gravitational pull', 'Balanced centripetal need and gravity.'),
        mcq(3, 'Increasing central body mass generally:', ['Increases orbital speed for same radius', 'Stops all motion', 'Removes gravity', 'Decreases pull'], 'Increases orbital speed for same radius', 'Stronger gravity requires faster orbit.'),
        mcq(4, 'Adding a third body can make orbits:', ['Less stable', 'Always circular', 'Unchanged always', 'Impossible'], 'Less stable', 'Three-body problem allows chaotic behaviour.'),
        tf(5, 'Gravity governs motion of planets and moons.', 'True', 'Gravitational attraction provides centripetal force.'),
        tf(6, 'All orbits are perfect circles in real systems.', 'False', 'Most orbits are elliptical to some degree.'),
        mcq(7, 'Gravity vectors show force direction:', ['Toward the other mass', 'Away from mass', 'Tangent to orbit only', 'Random'], 'Toward the other mass', 'Attraction acts along line joining centres.'),
        mcq(8, 'Too little speed for distance results in:', ['Elliptical or falling orbit', 'Escape always', 'No gravity', 'Square orbit'], 'Elliptical or falling orbit', 'Insufficient speed cannot maintain circular path.'),
        sa(9, 'Name one preset system in My Solar System.', 'Sun and Earth', 'Also binary stars and custom setups.'),
        sa(10, 'What real phenomenon relates to orbital motion and seasons?', "Earth's orbit around the Sun", 'Tilt and orbit affect seasonal sunlight.'),
    ],
    'photoelectric-effect' => [
        mcq(1, 'Photoelectric emission requires light frequency above:', ['The threshold frequency', 'Any frequency', 'Zero intensity only', 'Infinite wavelength'], 'The threshold frequency', 'Photon energy must exceed work function.'),
        mcq(2, 'Photon energy is given by:', ['E = hf', 'E = mc² only', 'E = Fd', 'E = ½mv only'], 'E = hf', 'Energy proportional to frequency.'),
        mcq(3, 'Increasing light intensity at fixed frequency above threshold:', ['Increases photocurrent', 'Increases electron kinetic energy', 'Lowers work function', 'Stops emission'], 'Increases photocurrent', 'More photons eject more electrons; energy per electron unchanged.'),
        mcq(4, 'Work function is:', ['Minimum energy to eject an electron', 'Maximum kinetic energy', 'Battery voltage', 'Half-life'], 'Minimum energy to eject an electron', 'Material-specific binding energy.'),
        tf(5, 'Einstein explained photoelectric effect using photons.', 'True', 'Light quantized in packets explained threshold behaviour.'),
        tf(6, 'Below threshold frequency, higher intensity always ejects electrons.', 'False', 'Individual photon energy matters, not just intensity.'),
        mcq(7, 'Changing metal target changes:', ['Work function', 'Speed of light', 'Electron charge', 'Planck constant'], 'Work function', 'Different metals have different surface binding.'),
        mcq(8, 'Solar cells apply photoelectric principles to:', ['Convert light to electrical energy', 'Split nuclei', 'Measure pH', 'Create interference'], 'Convert light to electrical energy', 'Photons liberate charge carriers for current.'),
        sa(9, 'Write the equation for photon energy.', 'E = hf', 'h is Planck constant; f is frequency.'),
        sa(10, 'What particle is ejected in the photoelectric effect?', 'electron', 'Photons knock out surface electrons.'),
    ],
    'wave-interference' => [
        mcq(1, 'Constructive interference occurs when waves are:', ['In phase', 'Out of phase', 'Perpendicular only', 'Absent'], 'In phase', 'Crests align to produce larger amplitude.'),
        mcq(2, 'Destructive interference occurs when waves are:', ['Out of phase', 'In phase', 'Same amplitude always', 'Stationary only'], 'Out of phase', 'Crest meets trough for cancellation.'),
        mcq(3, 'Diffraction is spreading of waves after passing through:', ['A gap or slit', 'A vacuum only', 'A capacitor', 'A nucleus'], 'A gap or slit', 'Waves bend around edges and through openings.'),
        mcq(4, 'Fringe spacing in double-slit interference depends on:', ['Wavelength and slit separation', 'Nuclear charge', 'Concentration', 'Work function'], 'Wavelength and slit separation', 'λ and d set angular positions of maxima.'),
        tf(5, 'Interference requires coherent wave sources.', 'True', 'Stable phase relation needed for sustained pattern.'),
        tf(6, 'Narrower slits produce narrower diffraction patterns.', 'False', 'Narrower slits give wider diffraction spread.'),
        mcq(7, 'Biological echolocation uses wave:', ['Reflection and interference principles', 'Nuclear decay', 'Beer-Lambert law', 'Alpha emission'], 'Reflection and interference principles', 'Bats use reflected sound waves to locate prey.'),
        mcq(8, 'Two sources closer together generally produce:', ['Wider fringe spacing', 'No fringes', 'Shorter wavelength', 'Higher atomic number'], 'Wider fringe spacing', 'Smaller d increases fringe separation.'),
        sa(9, 'Name one type of interference zone.', 'constructive', 'Also destructive regions.'),
        sa(10, 'What medical imaging uses wave reflection?', 'ultrasound', 'Ultrasound uses reflected sound waves.'),
    ],
];

$fileConfigs = [
    'phet_mathematics_remediation.json' => [
        'subject' => 'Mathematics',
        'description' => 'TIE Mathematics competence 15.1.1 (Form I remediation).',
        'slugs' => ['arithmetic', 'fractions-intro', 'fraction-matcher', 'fractions-equality', 'area-model-introduction'],
    ],
    'phet_mathematics_extension.json' => [
        'subject' => 'Mathematics',
        'description' => 'TIE Mathematics competences 16.1.1, 16.2.1 (Additional Mathematics extension).',
        'slugs' => ['calculus-grapher', 'fourier-making-waves'],
    ],
    'phet_physics_extension.json' => [
        'subject' => 'Physics',
        'description' => 'TIE Physics competence 13.1.1 (Form IV extension / advanced).',
        'slugs' => [
            'alpha-decay', 'beta-decay', 'blackbody-spectrum', 'capacitor-lab-basics',
            'circuit-construction-kit-ac', 'hydrogen-atom', 'keplers-laws',
            'models-of-the-hydrogen-atom', 'neon-lights-and-other-discharge-lamps',
            'nuclear-fission', 'quantum-measurement', 'quantum-wave-interference',
        ],
    ],
    'phet_chemistry_extension.json' => [
        'subject' => 'Chemistry',
        'description' => 'TIE Chemistry competences 12.1.1, 12.2.1 (Forms IV extension).',
        'slugs' => ['atomic-spectroscopy', 'beers-law-lab'],
    ],
    'phet_biology_extension.json' => [
        'subject' => 'Biology',
        'description' => 'TIE Biology competence 11.1.1 (cross-disciplinary).',
        'slugs' => ['charges-and-fields', 'my-solar-system', 'photoelectric-effect', 'wave-interference'],
    ],
];

$outputDir = $basePath.'/data/questions';
$totalExperiments = 0;
$totalQuestions = 0;
$filesCreated = 0;

foreach ($fileConfigs as $filename => $config) {
    $entries = [];
    foreach ($config['slugs'] as $slug) {
        if (! isset($experiments[$slug])) {
            fwrite(STDERR, "Missing experiment metadata for slug: {$slug}\n");
            exit(1);
        }
        if (! isset($questionSets[$slug])) {
            fwrite(STDERR, "Missing questions for slug: {$slug}\n");
            exit(1);
        }
        $entries[] = makeEntry(
            $experiments[$slug],
            $config['subject'],
            $config['description'],
            $questionSets[$slug]
        );
        $totalExperiments++;
        $totalQuestions += count($questionSets[$slug]);
    }

    $path = $outputDir.'/'.$filename;
    file_put_contents($path, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
    $filesCreated++;
    echo "Wrote {$filename}: ".count($entries)." experiments\n";
}

echo "\nTotals: {$filesCreated} files, {$totalExperiments} experiments, {$totalQuestions} questions\n";
