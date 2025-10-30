# Voorbereiden op React 19: een handleiding voor WordPress 6.6 gebruikers

## Inhoudsopgave

- [Verwijderde afschrijvingen in React](#verwijderde-afschrijvingen-in-react)
- [Verwijderde afschrijvingen in React DOM](#verwijderde-afschrijvingen-in-react-dom)
- [Afgeschreven TypeScript types verwijderd](#afgeschreven-typescript-types-verwijderd)

## Introductie

Als WordPress ontwikkelaars integreren we vaak custom React componenten in onze thema's en plugins om dynamische en responsieve gebruikersinterfaces te maken.

Met de [komende release van React 19](https://react.dev/blog/2024/04/25/react-19) is het cruciaal om je voor te bereiden op veranderingen en afschrijvingen (deprecations) die van invloed kunnen zijn op onze bestaande codebases. WordPress 6.6, die op 16 juli wordt uitgebracht, bevat React 18.3. Deze versie is bijna identiek aan 18.2, maar voegt waarschuwingen toe voor afgeschreven functies om je voor te bereiden op React 19.

> **Info**
> Om te helpen met de upgrade heeft het React team samengewerkt met het team van [Codemod](http://codemod.com) om codemods te publiceren die je code automatisch bijwerken naar veel van de nieuwe API's en patronen in React 19.
>
> Alle codemods zijn beschikbaar in de [react-codemod repo](https://github.com/reactjs/react-codemod) op GitHub. We zullen ook het codemod-commando van elke afschrijving bijvoegen (indien beschikbaar) om je te helpen je code automatisch bij te werken.

## Verwijderde afschrijvingen in React

Verschillende afgeschreven API's en functies zijn verwijderd om de React bibliotheek te stroomlijnen en best practices aan te moedigen. Deze sectie behandelt de belangrijkste wijzigingen en hoe je je code dienovereenkomstig kunt bijwerken.

### 1. Verwijdering van defaultProps voor functiecomponenten

React 19 verwijdert `defaultProps` voor functiecomponenten ten gunste van ES6 standaardparameters. Volgens het [WordPress team](https://make.wordpress.org/core/2024/06/07/preparation-for-react-19-upgrade/) wordt deze afschrijving het meest gebruikt in plugins en thema's.

**Voorbeeld met defaultProps:**

```jsx
function CustomButton({ label, color }) {
    return <button style={{ backgroundColor: color }}>{ label }</button>;
}

CustomButton.defaultProps = {
    label: 'Click me',
    color: 'blue',
};
```

**Bijgewerkte code met ES6 standaardparameters:**

```jsx
function CustomButton({ label = 'Click me', color = 'blue' }) {
    return <button style={{ backgroundColor: color }}>{ label }</button>;
}
```

### 2. Verwijdering van propTypes voor functiecomponenten

`propTypes` was [afgeschreven in React 15.5.0](https://legacy.reactjs.org/blog/2017/04/07/react-v15.5.0.html#new-deprecation-warnings) en zal volledig worden verwijderd in het React pakket van v19.

**Voorbeeld met propTypes:**

```jsx
import PropTypes from 'prop-types';

function CustomButton({ label, color }) {
    return <button style={{ backgroundColor: color }}>{ label }</button>;
}

CustomButton.defaultProps = {
    label: 'Click me',
    color: 'blue',
};

CustomButton.propTypes = {
    label: PropTypes.string,
    color: PropTypes.string,
};
```

**Bijgewerkte code met TypeScript:**

```jsx
type CustomButtonProps = {
    label?: string;
    color?: string;
};

const CustomButton = ({ label = 'Click me', color = 'blue' }: CustomButtonProps) => {
    return <button style={{ backgroundColor: color }}>{ label }</button>;
};
```

> **Info**
> Om je te helpen over te stappen van het gebruik van `propTypes` naar TypeScript, kun je het volgende codemod commando gebruiken:
>
> ```bash
> npx codemod@latest react/prop-types-typescript
> ```

### 3. Verwijdering van verouderde context (contextTypes en getChildContext)

Legacy Context [in React 16.6.0 is afgeschreven](https://legacy.reactjs.org/blog/2018/10/23/react-v-16-6.html) en in React v19 zal worden verwijderd.

**Verouderde context API:**

```jsx
import PropTypes from 'prop-types';

class SettingsProvider extends React.Component {
  static childContextTypes = {
    siteTitle: PropTypes.string.isRequired,
  };

  getChildContext() {
    return { siteTitle: 'My WordPress Site' };
  }

  render() {
    return <SettingsConsumer />;
  }
}

class SettingsConsumer extends React.Component {
  static contextTypes = {
    siteTitle: PropTypes.string.isRequired,
  };

  render() {
    return <div>Site Title: {this.context.siteTitle}</div>;
  }
}
```

**Moderne context API:**

```jsx
import React from 'react';

const SettingsContext = React.createContext();

class SettingsProvider extends React.Component {
  render() {
    return (
      <SettingsContext value={{ siteTitle: 'My WordPress Site' }}>
        <SettingsConsumer />
      </SettingsContext>
    );
  }
}

class SettingsConsumer extends React.Component {
  static contextType = SettingsContext;

  render() {
    const { siteTitle } = this.context;
    return <div>Site Title: { siteTitle }</div>;
  }
}
```

### 4. Verwijdering van string refs

String refs waren [legacy sinds React 16.3.0](https://legacy.reactjs.org/blog/2018/03/27/update-on-async-rendering.html) en zullen worden verwijderd in v19.

**Voorbeeld met string refs:**

```jsx
class CustomBlock extends React.Component {
  componentDidMount() {
    this.refs.input.focus();
  }

  render() {
    return <input ref="input" placeholder="Enter text..." />;
  }
}
```

**Bijgewerkte code met callback ref:**

```jsx
class CustomBlock extends React.Component {
  componentDidMount() {
    this.input.focus();
  }

  render() {
    return <input ref={(input) => (this.input = input)} placeholder="Enter text..." />;
  }
}
```

> **Info**
> Om je te helpen over te schakelen van het gebruik van string refs naar callback `ref`, kun je het volgende codemod commando gebruiken:
>
> ```bash
> npx codemod@latest react/19/replace-string-ref
> ```

### 5. Verwijdering van module pattern factories

Module pattern factories werden [afgeschreven in React 16.9.0](https://legacy.reactjs.org/blog/2019/08/08/react-v16.9.0.html#deprecating-module-pattern-factories) en worden verwijderd in React 19.

**Module pattern factory:**

```jsx
function SettingsPanelFactory() {
  return {
    render() {
      return (
        <div className="settings-panel">
          <h2>Settings</h2>
          {/* other settings UI components */}
        </div>
      );
    }
  };
}
```

**Reguliere functie:**

```jsx
function SettingsPanel() {
  return (
    <div className="settings-panel">
      <h2>Settings</h2>
      {/* other settings UI components */}
    </div>
  );
}
```

### 6. Verwijdering van createFactory API

`React.createFactory` wordt verwijderd in React 19.

**Voorbeeld met createFactory:**

```jsx
import { createFactory } from 'react';

const button = createFactory('button');

function CustomButton() {
  return button({ className: 'custom-button', type: 'button' }, 'Click Me');
}
```

**Bijgewerkte code met JSX:**

```jsx
function CustomButton() {
  return <button className="custom-button" type="button">Click Me</button>;
}
```

### 7. Verwijdering van react-test-renderer/shallow

React 19 verwijdert `react-test-renderer/shallow`.

**Oude test met shallow renderer:**

```jsx
import ShallowRenderer from 'react-test-renderer/shallow';

test('MyComponent shallow render', () => {
  const renderer = new ShallowRenderer();
  renderer.render(<MyComponent />);
  const result = renderer.getRenderOutput();
  expect(result.type).toBe('div');
});
```

**Bijgewerkte test met react-shallow-renderer:**

```bash
npm install react-shallow-renderer --save-dev
```

```jsx
import ShallowRenderer from 'react-shallow-renderer';

test('MyComponent shallow render', () => {
  const renderer = new ShallowRenderer();
  renderer.render(<MyComponent />);
  const result = renderer.getRenderOutput();
  expect(result.type).toBe('div');
});
```

**Aanbevolen: React Testing Library:**

```bash
npm install @testing-library/react --save-dev
```

```jsx
import { render, screen } from '@testing-library/react';
import MyBlock from './MyBlock';

test('MyBlock renders correctly', () => {
  render(<MyBlock />);
  const element = screen.getByText('MyBlock content');
  expect(element).toBeInTheDocument();
});
```

## Verwijderde afschrijvingen in React DOM

React DOM is ook veranderd in React 19, waarbij bepaalde afgeschreven methoden zijn verwijderd.

### 1. Verwijdering van react-dom/test-utils API

De [react-dom/test-utils](https://react.dev/warnings/react-dom-test-utils) API wordt verwijderd in React 19.

**Import bijwerken:**

```jsx
// Before
import { act } from 'react-dom/test-utils';

// Now
import { act } from 'react';
```

> **Info**
> Om je te helpen over te schakelen van het gebruik van `react-dom/test-utils` naar de nieuwe import, kun je het volgende codemod commando gebruiken:
>
> ```bash
> npx codemod@latest react/19/replace-act-import
> ```

**renderIntoDocument vervangen:**

```jsx
// Before
import { renderIntoDocument } from 'react-dom/test-utils';
renderIntoDocument(<Component />);

// Now
import { render } from '@testing-library/react';
render(<Component />);
```

**Simulate vervangen:**

```jsx
// Before
import { Simulate } from 'react-dom/test-utils';
const element = document.querySelector('button');
Simulate.click(element);

// Now
import { fireEvent } from '@testing-library/react';
const element = document.querySelector('button');
fireEvent.click(element);
```

### 2. Verwijdering van findDOMNode API

`ReactDOM.findDOMNode` wordt verwijderd in React 19.

**Voorbeeld met findDOMNode:**

```jsx
import { findDOMNode } from 'react-dom';

function AutoselectingInput() {
  useEffect(() => {
    const input = findDOMNode(this);
    input.select()
  }, []);

  render() {
    return <input defaultValue="Hello" />;
  }
}
```

**Bijgewerkte code met ref:**

```jsx
import React, { useEffect, useRef } from 'react';

function AutoselectingInput() {
  const inputRef = useRef(null);

  useEffect(() => {
    inputRef.current.select();
  }, []);

  return <input ref={inputRef} defaultValue="Hello" />;
}
```

### 3. Verwijdering van render API

`ReactDOM.render` wordt verwijderd in React 19.

**Oude render methode:**

```jsx
import { render } from 'react-dom';
render(<App />, document.getElementById('root'));
```

**Nieuwe createRoot API:**

```jsx
import { createRoot } from 'react-dom/client';
const root = createRoot(document.getElementById('root'));
root.render(<App />);
```

> **Info**
> Om je te helpen over te schakelen van het gebruik van `ReactDOM.render` naar de `createRoot` API van `react-dom/client`, kun je het volgende codemod commando gebruiken:
>
> ```bash
> npx codemod@latest react/19/replace-reactdom-render
> ```

### 4. Verwijdering van unmountComponentAtNode API

`ReactDOM.unmountComponentAtNode` wordt verwijderd in React 19.

```jsx
// Before
unmountComponentAtNode(document.getElementById('root'));

// Now
root.unmount();
```

### 5. Verwijdering van hydrate API

`ReactDOM.hydrate` wordt verwijderd in React 19.

**Oude hydrate methode:**

```jsx
import { hydrate } from 'react-dom';
import App from './App.js';

hydrate(
  <App />,
  document.getElementById('root')
);
```

**Nieuwe hydrateRoot API:**

```jsx
import { hydrateRoot } from 'react-dom/client';
import App from './App.js';

hydrateRoot(
  document.getElementById('root'),
  <App />
);
```

## Afgeschreven TypeScript types verwijderd

WordPress ontwikkelaars gebruiken vaak TypeScript om typeveiligheid toe te voegen en de kwaliteit van de code in React componenten te verbeteren.

**Automatische update tool:**

```bash
npx types-react-codemod@latest preset-19 ./path-to-app
```

### 1. Ref opruiming vereist

```tsx
// Before
(instance = current)} />

// Now
{ instance => current }} />
```

### 2. useRef vereist een argument

```tsx
// Before — @ts-expect-error: Expected 1 argument but saw none
useRef();

// Now — correct usage with an argument
useRef(undefined);
```

### 3. Wijzigingen in het ReactElement TypeScript

```tsx
// Previously, this was 'any'
type Example = ReactElement["props"];

// Now, this is 'unknown'
type Example = ReactElement["props"];
```

## Samenvatting

Voor WordPress ontwikkelaars is het cruciaal om op de hoogte te blijven van de laatste ontwikkelingen in React. Deze gids zorgt ervoor dat je de verschillende veranderingen in React begrijpt, zodat je ze kunt toepassen in je WordPress projecten.

Nog een laatste stukje informatie: Met React 19 zal de nieuwe JSX transform nodig zijn. Het goede nieuws is dat deze [al wordt meegeleverd met WordPress 6.6](https://make.wordpress.org/core/2024/06/06/jsx-in-wordpress-6-6/). Als de nieuwe transform niet is ingeschakeld, zie je deze waarschuwing:

```bash
Your app (or one of its dependencies) is using an outdated JSX transform. Update to the modern JSX transform for faster performance: https://react.dev/link/new-jsx-transform
```

Je hoeft alleen maar te stoppen met het gebruik van React imports voor JSX transformaties, omdat ze niet langer nodig zijn.
