# TEST - IT MNF

A tool for cleaning strings and generating PDF.

## Clean brackets

A webpage (to be found at `/brackets`) where the user can input multiple strings, one per line, and click on
a button to obtain the same strings, with all the external matching round brackets removed. 

**Examples:**

| Input         | Output        |
|---------------|---------------|
| (abc)         | abc           |
| (ab           | (ab           |
| (((abc)d))    | (abc)d        |
| (((abc))d))   | (((abc))d))   |

**Assumption:** If the brackets are unbalanced, the string remains unmodified (see the example in the last row).

## Clean matching chars

A webpage (to be found at `/pairs-en`) the user can use to remove external matching letter pairs
of the english alphabet (az, by, cx, dw, ...).

**Examples:**

| Input                            | Output                             |
|---------------                   |---------------                     |
| man                              | a                                  |
| keep                             | ee                                 |
| gqwertyuioplkjhgfdsazxcvbnm:?t   | qwertyuioplkjhgfdsazxcvbnm:?       |
| Abcdefghijklmnopqrstuvwxyz       |                                    |

## Printing PDF

From the `brackets` page, the user can generate a PDF containing the cleaned strings arranged on a square spiral asynchronously.
When the PDF is ready, a download link is displayed on the page.

![Alt text](/doc/img001.png)

![Alt text](/doc/img002.png)

**Assumption:** To print the spiral, the input strings are sorted by decreasing length, and dashes (`-`) are added so that each string is one character longer than the following one.

**Features:**

- When the strings to be printed in the PDF are too long, the font size and the distance between characters are reduced to fit the spiral on a single page.
- A more efficient algorithm for adding dashes to strings — which only ensures that all strings have different lengths — is available in the [utils/printpdf.php](utils/printpdf.php) file (commented out). See the image below for an example of the output using this implementation.

![Alt text](/doc/img003.png)

## Implementation Details

### Algorithms

#### External brackets removal

Explanation of how the function `cleanPairs` works.\
Refer to [brackets/index.php](brackets/index.php) for full implementation.

First, the function checks if the given string starts with an open bracket and ends with a closed bracket. If not, nothing to do.

```javascript
const len = str.length;
if(len == 0 || (str[0] != '(' || str[len-1] != ')')) return str;
```

Then, the function starts iterating over the array. 
First, it counts how many consecutive open brackets are at the beginning of the string, saving the index of the first character that is not an open bracket in the variable `opened`.

```javascript
var opened = -1;
var count = 0;
for (let i = 0; i < len; i++) {

    if(opened == -1) {
        if(str[i] != '(') {
            opened = i;
        } else {
            continue;
        }
    }
```

Once the length of the first sequence of open brackets is computed, the function proceeds to the second part, which determines whether the brackets are balanced or not.\
The function stores the opposite of the brackets nesting level in the variable `count`.
If `count` becomes positive before reaching the last `opened` characters of the string, it means that more brackets have been closed than were opened in the initial consecutive sequence of opening brackets. 
In this case, we must subtract them from the number of brackets considered for removal (`opened`).

If `opened` becomes less than or equal to 0, there are no more brackets to remove, and we can stop looping through the array.

```javascript
    if(str[i] == '(') count--;
    if(str[i] == ')') count++;

    if(count > 0 && i < len - opened) {
        opened -= 1;
        count = 0;

        // In this case there are no more initial open brackets that need to be checked
        if(opened <= 0) break;
    }
}
```

Now that the function has computed `opened` and `count`, we can proceed with the removal of the brackets if `opened` is equal to `count`.
In fact, this condition ensures that the brackets are balanced, and that the closed brackets matching the first `opened` open ones are at the end of the string.

```javascript
if(opened > 0 && opened == count) {
    return str.slice(opened, -count);
}
```

-----

#### External matching letter pairs removal

Explanation of how the function `cleanPairs` works.\
Refer to [pairs-en/index.php](pairs-en/index.php) for full implementation.

First, we have the function `match`, which checks whether two given chars constitute a matching letter pairs of the english alphabet.

```javascript
function match(v1, v2) {
    let char1 = v1.toLowerCase().charCodeAt(0);
    let char2 = v2.toLowerCase().charCodeAt(0);
    if(char1 >= 97 && char2 >= 97 && char1 <= 122 && char2 <= 122 && char1 + char2 == 219) 
        return true;
    return false;
}
```

The `cleanPairs` function iterates through the array up to the floor of its length divided by 2, checking step by step whether the character at index `i` matches the character at index `len - i - 1`.
As soon as it finds a pair that does not match, it stops, and the index `i` represents the number of characters to be removed from the start and from the end of the string.

```javascript
const len = str.length;
var i;
for(i = 0; i < Math.floor(len / 2); i++) {
    if(!match(str[len - i - 1], str[i]))
        break;
}

if(i > 0)
    return str.slice(i, -i);
return str;
```

-----

#### Generating square spiral

Explanation of the generation of the square spiral.\
Refer to [utils/printpdf.php](utils/printpdf.php) for full implementation.

##### Adding dashes to the received string array

In order to generate the square spiral, all strings must have different length. \
As mentioned earlier, we assume that to generate the spiral each string should be one character longer than the next one.

The script first sorts all received strings by length. 
Then it computes the minimum length `$q` that the first string should have so that each string is one character longer than the next one.
Finally, it adds the correct number of dashes to the strings.

```php
// Sorting values by string length
usort($values, fn($v1, $v2) => strlen($v2) <=> strlen($v1) );

// Adding dashes to the strings so that each string has a length exactly one greater than the following one
$q = -1;
for($i = 0; $i < count($values); $i++) {
    $q = max($q, strlen($values[$i]) + $i);
}
for($i = 0; $i < count($values); $i++) {
    $values[$i] .= str_repeat('-', $q - strlen($values[$i]) - $i);
}
```

##### Printing the square spiral

Now that all strings have the correct length, we can proceed to print the spiral.

The algorithm iterates over the array of strings, and for each string, it iterates over its characters. 
Each character is printed at the position specified by the variables `$x` and `$y`. The position is then updated by adding the offset multiplied by the direction vector (a two-coordinate vector, where each coordinate can be either 1 or -1).

```php
$x = $startx;
$y = $starty;
$direction = $startdirection;
for ($i = 0; $i < count($values); $i++) {
    $cur = $values[$i];
    for($j = 0; $j < strlen($cur); $j++) {
        $pdf->SetXY($x += $offset * $direction[0], $y += $offset * $direction[1]);
        $pdf->Write($offset, $cur[$j]);
    }
    $direction = getnextdirection($direction);
}
```

Each time a string is printed, the direction vector is updated.

```php
function getnextdirection($direction) {
    if($direction[0] != 0) return [$direction[1], $direction[0]];
    if($direction[1] != 0) return [-$direction[1], $direction[0]];
}
```


### Stack and Technological Choices

For both cleaning strings from external matching round brackets (`/brackets`), and external matching letter pairs (`/pairs-en`), the string processing is performed on the client side to reduce server requests.
The server is responsible only for generating the PDF using FPDF library ([link](https://fpdf.org/)). Once the file is created, a download link is returned to the page.

## Running the application

To run this project with Docker:

```bash
docker compose up -d --build
```

This command will:

- Build a container with Apache and PHP 8.2  
- Copy the source files into Apache’s working directory (`/var/www/html`)
- Start the server, exposing the application on port 80


## Scaling

Potential improvements to make the application more scalable:

- Add a task to delete previously generated old PDF.
- Add multiple instances of Apache PHP to handle more requests simultaneously.
- Move the PDF generation from PHP to a compiled language to handle requests faster.


